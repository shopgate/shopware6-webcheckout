<?php declare(strict_types=1);

namespace Shopgate\ConnectSW6\Storefront\Controller;

use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Shopgate\ConnectSW6\Services\CustomerManager;
use Shopgate\ConnectSW6\Services\TokenManager;
use Shopgate\ConnectSW6\Storefront\Page\GenericPageLoader;
use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Checkout\Customer\SalesChannel\AbstractLogoutRoute;
use Shopware\Core\Framework\Routing\Annotation\ContextTokenRequired;
use Shopware\Core\Framework\Routing\Annotation\LoginRequired;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\Framework\Validation\Exception\ConstraintViolationException;
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
    private AbstractLogoutRoute $logoutRoute;
    private TokenManager $tokenManager;
    private LoggerInterface $logger;
    private CustomerManager $customerManager;

    public function __construct(
        GenericPageLoader $genericPageLoader,
        AbstractLogoutRoute $logoutRoute,
        CustomerManager $customerManager,
        LoggerInterface $logger,
        TokenManager $tokenManager
    ) {
        $this->genericPageLoader = $genericPageLoader;
        $this->logoutRoute = $logoutRoute;
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

        try {
            $this->logoutRoute->logout($context, $dataBag);
        } catch (ConstraintViolationException $formViolations) {
            $parameters = array_merge($parameters, ['formViolations' => $formViolations]);
        }

        return $this->redirectToRoute('frontend.account.login.page', $parameters);
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

        return $this->renderStorefront('@ShopgateConnectSW6/sgconnect/page/registered.html.twig', [
            'page' => $page
        ]);
    }

    /**
     * @Route("/sgconnect/login", name="frontend.sgconnect.login", methods={"GET"})
     */
    public function login(Request $request, SalesChannelContext $context): Response
    {
        // an already logged in customer just needs a redirect
        $redirectPage = $request->query->get('redirectTo', 'frontend.checkout.confirm.page');
        $customer = $context->getCustomer();
        if ($customer && $customer->getGuest() === false) {
            return $this->redirectToRoute($redirectPage);
        }

        $token = $request->query->get('token');
        if (!$this->tokenManager->validateToken($token)) {
            $this->log(Logger::WARNING, $request, 'Token expired or invalid');
            // todo: best to redirect the user back to App
            return $this->renderStorefront('@Storefront/storefront/page/error/error-404.html.twig');
        }

        $customerId = $this->tokenManager->getCustomerId($token);
        // todo: best to redirect the user back to App
        if (!$customerId) {
            $this->log(Logger::ERROR, $request, 'Could not get customer ID from payload. Strange!');
            return $this->renderStorefront('@Storefront/storefront/page/error/error-404.html.twig');
        }

        $this->customerManager->loginCustomerById($customerId, $context);

        return $this->redirectToRoute($redirectPage, []);
    }

    /**
     * @RouteScope(scopes={"store-api"})
     * @ContextTokenRequired()
     * @LoginRequired()
     * @Route("/store-api/sgconnect/login/token", name="store-api.sgconnect.login.token", methods={"GET", "POST"})
     */
    public function loginToken(Request $request, CustomerEntity $customer): JsonResponse
    {
        return new JsonResponse($this->tokenManager->createToken($customer->getId(), $request->getHost()));
    }

    private function log(int $code, Request $request, string $message): void
    {
        $this->logger->log($code, $request->attributes->get('_route'), ['additionalData' => $message]);
    }
}
