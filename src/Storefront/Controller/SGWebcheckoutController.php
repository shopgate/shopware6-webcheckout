<?php declare(strict_types=1);

namespace Shopgate\WebcheckoutSW6\Storefront\Controller;

use Monolog\Logger;
use Psr\Log\LoggerInterface;
use ReallySimpleJWT\Exception\BuildException;
use Shopgate\WebcheckoutSW6\Services\CustomerManager;
use Shopgate\WebcheckoutSW6\Services\TokenManager;
use Shopgate\WebcheckoutSW6\Storefront\Page\GenericPageLoader;
use Shopware\Core\Framework\Routing\Annotation\ContextTokenRequired;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @RouteScope(scopes={"storefront"})
 * @Route(defaults={"_routeScope"={"storefront"}})
 */
class SGWebcheckoutController extends StorefrontController
{
    private GenericPageLoader $genericPageLoader;
    private TokenManager $tokenManager;
    private LoggerInterface $logger;
    private CustomerManager $customerManager;

    public function __construct(
        GenericPageLoader $genericPageLoader,
        CustomerManager $customerManager,
        LoggerInterface $logger,
        TokenManager $tokenManager
    ) {
        $this->genericPageLoader = $genericPageLoader;
        $this->tokenManager = $tokenManager;
        $this->logger = $logger;
        $this->customerManager = $customerManager;
    }

    /**
     * We could have a state where the customer is loggedOut of the App,
     * but loggedIn the inApp browser. If a customer decides to register
     * again we log them out first, and redirect them to the registration page.
     * SW6 allows the user to check out as guest, but that is only possible
     * when they are checking out & therefore directed to a special registry page.
     *
     * @Route("/sgwebcheckout/register", name="frontend.sgwebcheckout.register", methods={"GET"})
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

        return $this->redirectToRoute('frontend.account.login.page', array_merge($parameters, $violations));
    }

    /**
     * Route handles the "after" login state of the App user. It is just a loader
     * page that logsIn the customer in the App & closes the inApp browser.
     *
     * @Route("/sgwebcheckout/registered", name="frontend.sgwebcheckout.registered", methods={"GET"})
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

    /**
     * Note that an error is not shown to the customer of the App in case the token
     * is not good anymore. They are just redirected back to App.
     *
     * @Route("/sgwebcheckout/login", name="frontend.sgwebcheckout.login", methods={"GET"})
     */
    public function login(Request $request, SalesChannelContext $context, RequestDataBag $dataBag): Response
    {
        $token = $request->query->get('token', '');
        if (!$this->tokenManager->validateToken($token)) {
            $this->log(Logger::WARNING, $request, 'Token expired or invalid');
            $page = $this->genericPageLoader->load($request, $context);
            return $this->renderStorefront('@SgateWebcheckoutSW6/sgwebcheckout/page/spinner.html.twig', [
                'page' => $page
            ]);
        }
        if ($context->getCustomer()) {
            // if we don't log out, then context token can change. Which de-sync's /w App context token.
            $this->customerManager->logoutCustomer($context, $dataBag);
        }

        $customerId = $this->tokenManager->getCustomerId($token);
        if ($customerId) {
            $this->customerManager->loginCustomerById($customerId, $context);
        } else {
            $this->log(Logger::INFO, $request, 'Signing in as guest token');
            $contextToken = $this->tokenManager->getContextToken($token);
            $this->customerManager->loginByContextToken($contextToken, $request, $context);
        }

        return $this->getRedirect($request);
    }

    /**
     * This endpoint creates tokens for customers & guests
     *
     * @RouteScope(scopes={"store-api"})
     * @ContextTokenRequired()
     * @Route(defaults={"_routeScope"={"store-api"},"_contextTokenRequired"=true})
     * @Route("/store-api/sgwebcheckout/login/token", name="store-api.sgwebcheckout.login.token", methods={"GET", "POST"})
     * @throws BuildException
     */
    public function loginToken(Request $request, SalesChannelContext $context): JsonResponse
    {
        $customerId = $context->getCustomer() ? $context->getCustomer()->getId() : null;
        $this->log(Logger::DEBUG, $request, 'Token for customerId: ' . $customerId);
        return new JsonResponse(
            $this->tokenManager->createToken($context->getToken(), $request->getHost(), $customerId)
        );
    }

    private function log(int $code, Request $request, string $message): void
    {
        $this->logger->log($code, $request->attributes->get('_route'), ['additionalData' => $message]);
    }

    /**
     * Handles redirect with fallback
     */
    private function getRedirect(Request $request): RedirectResponse
    {
        $redirectPage = $request->query->get('redirectTo', 'frontend.checkout.confirm.page');
        $isCheckout = $request->get('sgcloud_checkout', '0');

        return $this->redirect(
            strpos($redirectPage, 'http') === false
                ? $this->generateUrl($redirectPage)
                : http_build_url($redirectPage, ['query' => 'sgcloud_checkout=' . $isCheckout], HTTP_URL_JOIN_QUERY)
        );
    }
}
