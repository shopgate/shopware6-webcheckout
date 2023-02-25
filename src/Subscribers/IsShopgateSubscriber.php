<?php declare(strict_types=1);

namespace Shopgate\WebcheckoutSW6\Subscribers;

use Shopware\Core\Framework\Struct\ArrayStruct;
use Shopware\Storefront\Pagelet\Header\HeaderPageletLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerEvent;

class IsShopgateSubscriber implements EventSubscriberInterface
{
    use ShopgateDetectTrait;

    public const IS_WEBCHECKOUT = 'IS_SHOPGATE_WEBCHECKOUT';
    public const IS_API_CALL = 'IS_SHOPGATE_API_CALL';
    public const SG_SESSION_KEY = 'sgWebView';

    public static function getSubscribedEvents(): array
    {
        return [
            HeaderPageletLoadedEvent::class => ['addPageData', 30],
            ControllerEvent::class => [['addIsShopgate', 40], ['checkShopgateApiCall', 50]]
        ];
    }

    public function addIsShopgate(ControllerEvent $event): void
    {
        if (!$this->isShopgate($event->getRequest())) {
            return;
        }
        $event->getRequest()->getSession()->set(self::SG_SESSION_KEY, 1);
        define(self::IS_WEBCHECKOUT, true);
    }

    public function addPageData(HeaderPageletLoadedEvent $event)
    {
        if (!$this->isShopgate($event->getRequest())) {
            return;
        }
        $event->getPagelet()->addExtension('sg_webcheckout_data', new ArrayStruct());
    }

    public function checkShopgateApiCall(ControllerEvent $event): void
    {
        $isWebcheckoutCall = strpos($event->getRequest()->getPathInfo(), 'api/sgwebcheckout') !== false;
        if ($isWebcheckoutCall || $this->isShopgateApiCall($event->getRequest())) {
            define(self::IS_API_CALL, true);
        }
    }
}
