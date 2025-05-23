<?php declare(strict_types=1);

namespace Shopgate\WebcheckoutSW6\Subscribers;

use Shopgate\WebcheckoutSW6\Storefront\Events\GenericPageLoadedEvent;
use Shopware\Core\Checkout\Order\SalesChannel\OrderService;
use Shopware\Core\Framework\Struct\ArrayStruct;
use Shopware\Storefront\Page\Account\Login\AccountLoginPageLoadedEvent;
use Shopware\Storefront\Page\Account\Order\AccountEditOrderPageLoadedEvent;
use Shopware\Storefront\Page\Account\Order\AccountOrderDetailPageLoadedEvent;
use Shopware\Storefront\Page\Account\Order\AccountOrderPageLoadedEvent;
use Shopware\Storefront\Page\Account\Overview\AccountOverviewPageLoadedEvent;
use Shopware\Storefront\Page\Account\Profile\AccountProfilePageLoadedEvent;
use Shopware\Storefront\Page\Account\RecoverPassword\AccountRecoverPasswordPageLoadedEvent;
use Shopware\Storefront\Page\Address\Detail\AddressDetailPageLoadedEvent;
use Shopware\Storefront\Page\Address\Listing\AddressListingPageLoadedEvent;
use Shopware\Storefront\Page\Checkout\Cart\CheckoutCartPageLoadedEvent;
use Shopware\Storefront\Page\Checkout\Confirm\CheckoutConfirmPageLoadedEvent;
use Shopware\Storefront\Page\Checkout\Finish\CheckoutFinishPageLoadedEvent;
use Shopware\Storefront\Page\Checkout\Register\CheckoutRegisterPageLoadedEvent;
use Shopware\Storefront\Page\Navigation\Error\ErrorPageLoadedEvent;
use Shopware\Storefront\Page\Navigation\NavigationPageLoadedEvent;
use Shopware\Storefront\Page\Newsletter\Subscribe\NewsletterSubscribePageLoadedEvent;
use Shopware\Storefront\Page\PageLoadedEvent;
use Shopware\Storefront\Page\Product\Configurator\ProductPageConfiguratorLoader;
use Shopware\Storefront\Page\Product\ProductPageLoadedEvent;
use Shopware\Storefront\Page\Wishlist\WishlistPageLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;

class IsShopgateSubscriber implements EventSubscriberInterface
{
    use ShopgateDetectTrait;

    final public const IS_WEBCHECKOUT = 'IS_SHOPGATE_WEBCHECKOUT';
    final public const IS_API_CALL = 'IS_SHOPGATE_API_CALL';
    final public const SG_SESSION_KEY = 'sgWebView';

    public static function getSubscribedEvents(): array
    {
        return [
            AccountEditOrderPageLoadedEvent::class => ['addPageData', 30],
            AccountLoginPageLoadedEvent::class => ['addPageData', 30],
            AccountOverviewPageLoadedEvent::class => ['addPageData', 30],
            AccountOrderDetailPageLoadedEvent::class => ['addPageData', 30],
            AccountOrderPageLoadedEvent::class => ['addPageData', 30],
            AccountProfilePageLoadedEvent::class => ['addPageData', 30],
            AccountRecoverPasswordPageLoadedEvent::class => ['addPageData', 30],
            AddressDetailPageLoadedEvent::class => ['addPageData', 30],
            AddressListingPageLoadedEvent::class => ['addPageData', 30],
            CheckoutCartPageLoadedEvent::class => ['addPageData', 30],
            CheckoutConfirmPageLoadedEvent::class => ['addPageData', 30],
            CheckoutFinishPageLoadedEvent::class => ['addPageData', 30],
            CheckoutRegisterPageLoadedEvent::class  => ['addPageData', 30],
            GenericPageLoadedEvent::class => ['addPageData', 30],
            NavigationPageLoadedEvent::class => ['addPageData', 30],
            NewsletterSubscribePageLoadedEvent::class => ['addPageData', 30],
            ProductPageLoadedEvent::class => ['addPageData', 30],
            PageLoadedEvent::class => ['addPageData', 30],
            WishlistPageLoadedEvent::class => ['addPageData', 30],
            ErrorPageLoadedEvent::class => ['addPageData', 30],
            ControllerEvent::class => [['addTrackers', 40], ['checkShopgateApiCall', 50]]
        ];
    }

    public function addTrackers(ControllerEvent $event): void
    {
        if (!$this->isShopgate($event->getRequest())) {
            return;
        }
        $event->getRequest()->getSession()->set(self::SG_SESSION_KEY, 1);
        $event->getRequest()->getSession()->set(OrderService::AFFILIATE_CODE_KEY, 'SGConnect_App');
        defined(self::IS_WEBCHECKOUT) || define(self::IS_WEBCHECKOUT, true);
    }

    public function addPageData(PageLoadedEvent $event): void
    {
        if (!$this->isShopgate($event->getRequest())) {
            return;
        }
        $data = ['isCodebaseV2' => $this->isNativeBase($event->getRequest())];
        $event->getPage()->addExtension('sg_webcheckout_data', new ArrayStruct($data));
    }

    public function checkShopgateApiCall(ControllerEvent $event): void
    {
        $isWebcheckoutCall = str_contains($event->getRequest()->getPathInfo(), 'api/sgwebcheckout');
        if ($isWebcheckoutCall || $this->isShopgateApiCall($event->getRequest())) {
            defined(self::IS_API_CALL) || define(self::IS_API_CALL, true);
        }
    }
}
