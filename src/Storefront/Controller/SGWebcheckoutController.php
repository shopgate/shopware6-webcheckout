<?php declare(strict_types=1);

namespace Shopgate\WebcheckoutSW6\Storefront\Controller;

use Monolog\Level;
use Psr\Log\LoggerInterface;
use ReallySimpleJWT\Exception\BuildException;
use ReallySimpleJWT\Exception\EncodeException;
use Shopgate\WebcheckoutSW6\Services\CustomerManager;
use Shopgate\WebcheckoutSW6\Services\TokenManager;
use Shopgate\WebcheckoutSW6\Storefront\Page\GenericPageLoader;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(defaults: ['_routeScope' => ['storefront']])]
class SGWebcheckoutController extends StorefrontController
{
    public function __construct(
        private readonly GenericPageLoader $genericPageLoader,
        private readonly CustomerManager $customerManager,
        private readonly LoggerInterface $logger,
        private readonly TokenManager $tokenManager
    ) {
    }

    #[Route(path: '/sgwebcheckout/register', name: 'frontend.sgwebcheckout.register', methods: ['GET'])]
    /**
     * We could have a state where the customer is loggedOut of the App,
     * but loggedIn the inApp browser. If a customer decides to register
     * again we log them out first, and redirect them to the registration page.
     * SW6 allows the user to check out as guest, but that is only possible
     * when they are checking out & therefore directed to a special registry page.
     */
    public function register(Request $request, SalesChannelContext $context, RequestDataBag $dataBag): RedirectResponse
    {
        // identifies the registration call coming from App's checkout page
        $isCheckout = $request->get('sgcloud_checkout') === '1';
        $parameters = array_merge(
            $isCheckout ? [] : ['redirectTo' => 'frontend.sgwebcheckout.registered'],
            $request->query->all()
        );

        // no need to check for guest here as we need to log out the "guest customer" as well
        if ($context->getCustomer() === null) {
            // redirect to checkouts' registration where the user has the option to select guest checkout
            $registerRoute = $isCheckout ? 'frontend.checkout.register.page' : 'frontend.account.login.page';

            return $this->redirectToRoute($registerRoute, $parameters);
        }

        $violations = $this->customerManager->logoutCustomer($context, $dataBag);
        $export = array_merge(
            $parameters,
            isset($violations['formViolations']) ? ['formViolations' => $violations['formViolations']] : []
        );

        return $this->redirectToRoute('frontend.account.login.page', $export);
    }

    #[Route(path: '/sgwebcheckout/registered', name: 'frontend.sgwebcheckout.registered', methods: ['GET'])]
    /**
     * Route handles the "after" login state of the App user. It is just a loader
     * page that logsIn the customer in the App & closes the inApp browser.
     */
    public function registered(Request $request, SalesChannelContext $context): Response
    {
        $customer = $context->getCustomer();
        if ($customer === null || $customer->getGuest() === true) {
            return $this->renderStorefront('@Storefront/storefront/page/error/error-404.html.twig');
        }
        $page = $this->genericPageLoader->load($request, $context);

        return $this->renderStorefront('@SgateWebcheckoutSW6/sgwebcheckout/page/spinner.html.twig', [
            'page' => $page
        ]);
    }

    #[Route(path: '/sgwebcheckout/login', name: 'frontend.sgwebcheckout.login', methods: ['GET'])]
    /**
     * Note that an error is not shown to the customer of the App in case the token
     * is not good anymore. They are just redirected back to App.
     */
    public function login(Request $request, SalesChannelContext $context, RequestDataBag $dataBag): Response
    {
        $token = $request->query->get('token', '');
        if (!$this->tokenManager->validateToken($token)) {
            $this->log(Level::Warning, $request, 'Token expired or invalid');
            $page = $this->genericPageLoader->load($request, $context);
            return $this->renderStorefront('@SgateWebcheckoutSW6/sgwebcheckout/page/spinner.html.twig', [
                'page' => $page
            ]);
        }
        $currentToken = $this->tokenManager->getContextToken($token);
        // case where a customer is logged in through a session cookie (inApp browser)
        // we log them out in case this is an attempt to register
        if ($context->getCustomer()) {
            $currentToken = $this->customerManager->logoutCustomer($context, $dataBag)['token'] ?? $currentToken;
        }

        // load customerId from encrypted token
        $customerId = $this->tokenManager->getCustomerId($token);
        if ($customerId) {
            $context = $this->customerManager->loginCustomerById($customerId, $context);
            $context->getToken() !== $currentToken && $dataBag->set('sgTokenNeedsSync', 1);
        } else {
            $this->log(Level::Info, $request, 'Signing in as guest token');
            $this->customerManager->loginByContextToken($currentToken, $request, $context);
        }

        return $this->getRedirect($request, $dataBag->all());
    }

    #[Route(path: '/store-api/sgwebcheckout/login/token', name: 'store-api.sgwebcheckout.login.token', defaults: [
        '_routeScope' => ['store-api'],
        '_contextTokenRequired' => true
    ], methods: [
        'GET',
        'POST'
    ])]
    /**
     * This endpoint creates tokens for customers & guests
     *
     * @throws BuildException
     * @throws EncodeException
     */
    public function loginToken(Request $request, SalesChannelContext $context): JsonResponse
    {
        $customerId = $context->getCustomer()?->getId();
        $this->log(Level::Info, $request, 'Token for customerId: ' . $customerId);
        return new JsonResponse(
            $this->tokenManager->createToken($context->getToken(), $request->getHost(), $customerId)
        );
    }

    private function log(Level $code, Request $request, string $message): void
    {
        $this->logger->log($code->value, $request->attributes->get('_route'), ['additionalData' => $message]);
    }

    /**
     * Handles redirect with fallback
     */
    private function getRedirect(Request $request, array $parameters = []): RedirectResponse
    {
        $redirectPage = $request->query->get('redirectTo', 'frontend.checkout.confirm.page');
        $isCheckout = $request->get('sgcloud_checkout', '0');
        $params = array_merge(['sgcloud_checkout' => $isCheckout], $parameters);
        $query = http_build_query($params);

        return !str_contains($redirectPage, 'http')
            ? $this->redirectToRoute($redirectPage, $params)
            : $this->redirect(http_build_url($redirectPage, ['query' => $query], HTTP_URL_JOIN_QUERY));
    }
}
