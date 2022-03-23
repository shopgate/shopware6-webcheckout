<?php declare(strict_types=1);

namespace Shopgate\ConnectSW6\Storefront\Controller;

use Psr\Log\LoggerInterface;
use Shopgate\ConnectSW6\Services\TokenManager;
use Shopgate\ConnectSW6\Storefront\Page\GenericPageLoader;
use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Checkout\Customer\SalesChannel\AbstractLogoutRoute;
use Shopware\Core\Framework\Api\Response\JsonApiResponse;
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

    public function __construct(
        GenericPageLoader $genericPageLoader,
        AbstractLogoutRoute $logoutRoute,
        LoggerInterface $logger,
        TokenManager $tokenManager
    ) {
        $this->genericPageLoader = $genericPageLoader;
        $this->logoutRoute = $logoutRoute;
        $this->tokenManager = $tokenManager;
        $this->logger = $logger;
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
     * @RouteScope(scopes={"store-api"})
     * @ContextTokenRequired()
     * @LoginRequired()
     * @Route("/store-api/sgconnect/login/token", name="store-api.sgconnect.login.token", methods={"GET", "POST"})
     */
    public function loginToken(Request $request, CustomerEntity $customer)
    {
        if (!$this->tokenManager->isValidSecret()) {
            $this->logger->critical(
                $request->attributes->get('route'),
                ['additionalData' => 'Insecure secret, please read README.md of our module']
            );
            return new JsonApiResponse(
                ['error' => 'SGCONNECT Secret error'],
                Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        $token = $this->tokenManager->createToken($customer->getId(), $request->getHost());

        return new JsonResponse(['token' => $token]);
    }
}
