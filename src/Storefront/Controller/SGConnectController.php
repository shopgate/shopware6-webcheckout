<?php declare(strict_types=1);

namespace Shopgate\ConnectSW6\Storefront\Controller;

use Shopgate\ConnectSW6\Storefront\Page\GenericPageLoader;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @RouteScope(scopes={"storefront"})
 */
class SGConnectController extends StorefrontController
{
    private GenericPageLoader $genericPageLoader;

    public function __construct(GenericPageLoader $genericPageLoader)
    {
        $this->genericPageLoader = $genericPageLoader;
    }

    /**
     * @Route("/sgconnect/registered", name="frontend.sgconnect.registered", methods={"GET"})
     */
    public function registered(Request $request, SalesChannelContext $context): Response
    {
        if (!$context->getCustomer()) {
            return $this->renderStorefront('@Storefront/storefront/page/error/error-404.html.twig');
        }
        $page = $this->genericPageLoader->load($request, $context);

        return $this->renderStorefront('@ShopgateConnectSW6/storefront/page/shopgate/registered.html.twig', [
            'page' => $page
        ]);
    }
}
