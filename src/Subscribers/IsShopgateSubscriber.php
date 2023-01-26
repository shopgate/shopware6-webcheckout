<?php declare(strict_types=1);

namespace Shopgate\WebcheckoutSW6\Subscribers;

use Shopware\Core\Framework\Struct\ArrayStruct;
use Shopware\Storefront\Pagelet\Header\HeaderPageletLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;

class IsShopgateSubscriber implements EventSubscriberInterface
{
    use ShopgateDetectTrait;

    public const SG_SESSION_KEY = 'sgWebView';

    public static function getSubscribedEvents(): array
    {
        return [HeaderPageletLoadedEvent::class => 'addIsShopgate'];
    }

    public function addIsShopgate(HeaderPageletLoadedEvent $event): void
    {
        $isShopgate = $this->isShopgate($event->getRequest());
        if (!$isShopgate) {
            return;
        }
        $event->getRequest()->getSession()->set(self::SG_SESSION_KEY, 1);
        $event->getPagelet()->addExtension('sg_webcheckout_data', new ArrayStruct());
    }

}
