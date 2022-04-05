<?php declare(strict_types=1);

namespace Shopgate\ConnectSW6\Storefront\Controller;

use Monolog\Logger;
use Psr\Log\LoggerInterface;
use ReallySimpleJWT\Exception\BuildException;
use Shopgate\ConnectSW6\Services\CustomerManager;
use Shopgate\ConnectSW6\Services\TokenManager;
use Shopgate\ConnectSW6\Storefront\Page\GenericPageLoader;
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
 */
class SGConnectController extends StorefrontController
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
     *
     * @Route("/sgconnect/register", name="frontend.sgconnect.register", methods={"GET"})
     */
    public function register(Request $request, SalesChannelContext $context, RequestDataBag $dataBag): RedirectResponse
    {
        $parameters = array_merge(
            ['redirectTo' => 'frontend.sgconnect.registered'],
            $request->query->all()
        );
        // no need to check for guest as we need to log them out as well
        if ($context->getCustomer() === null) {
            return $this->redirectToRoute('frontend.account.login.page', $parameters);
        }
        $violations = $this->customerManager->logoutCustomer($context, $dataBag);

        return $this->redirectToRoute('frontend.account.login.page', array_merge($parameters, $violations));
    }

    /**
     * Route handles the "after" login state of the App user. It is just a loader
     * page that logsIn the customer in the App & closes the inApp browser.
     *
     * @Route("/sgconnect/registered", name="frontend.sgconnect.registered", methods={"GET"})
     */
    public function registered(Request $request, SalesChannelContext $context): Response
    {
        $customer = $context->getCustomer();
        if ($customer === null || $customer->getGuest() === true) {
            return $this->renderStorefront('@Storefront/storefront/page/error/error-404.html.twig');
        }
        $page = $this->genericPageLoader->load($request, $context);

        return $this->renderStorefront('@ShopgateConnectSW6/sgconnect/page/spinner.html.twig', [
            'page' => $page
        ]);
    }

    /**
     * Helps logout the user from the
     *
     * @Route("/sgconnect/logout", name="frontend.sgconnect.logout", methods={"GET"})
     */
    public function logout(Request $request, SalesChannelContext $context, RequestDataBag $dataBag): Response
    {
        if ($context->getCustomer()) {
            $this->customerManager->logoutCustomer($context, $dataBag);
        }
        $page = $this->genericPageLoader->load($request, $context);

        return $this->renderStorefront('@ShopgateConnectSW6/sgconnect/page/spinner.html.twig', [
            'page' => $page
        ]);
    }

    /**
     * @Route("/sgconnect/login", name="frontend.sgconnect.login", methods={"GET"})
     */
    public function login(Request $request, SalesChannelContext $context): Response
    {
        $token = $request->query->get('token', '');
        if (!$this->tokenManager->validateToken($token)) {
            $this->log(Logger::WARNING, $request, 'Token expired or invalid');
            $page = $this->genericPageLoader->load($request, $context);
            return $this->renderStorefront('@ShopgateConnectSW6/sgconnect/page/spinner.html.twig', [
                'page' => $page
            ]);
        }

        $customerId = $this->tokenManager->getCustomerId($token);
        if ($customerId) {
            $this->customerManager->loginCustomerById($customerId, $context);
        } else {
            $this->log(Logger::INFO, $request, 'Signing in as guest token');
            $contextToken = $this->tokenManager->getContextToken($token);
            $this->customerManager->loginByContextToken($contextToken, $request, $context);
            $this->logoutOnDeSync($request);
        }

        return $this->getRedirect($request);
    }

    /**
     * This endpoint creates tokens for customers & guests
     *
     * @RouteScope(scopes={"store-api"})
     * @ContextTokenRequired()
     * @Route("/store-api/sgconnect/login/token", name="store-api.sgconnect.login.token", methods={"GET", "POST"})
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
        return $this->redirect(
            strpos($redirectPage, 'http') === false ? $this->generateUrl($redirectPage) : $redirectPage
        );
    }

    /**
     * Sometimes the App might have a strange state where the guest thinks
     * he is an authenticated user (logged in the App, but not SW API).
     * We need to fix the App state by logging the user out in the App.
     */
    private function logoutOnDeSync(Request $request): void
    {
        if ($request->query->get('isLoggedIn', 'false') === 'true') {
            $this->log(Logger::DEBUG, $request, 'De-synced App & API logged in states');
            $request->query->set('redirectTo', 'frontend.sgconnect.logout');
        }
    }
}
