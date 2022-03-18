<?php

declare(strict_types=1);

namespace Shopgate\ConnectSW6\Subscribers;

use Shopware\Core\Framework\Struct\ArrayStruct;
use Shopware\Storefront\Pagelet\Header\HeaderPageletLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class IsShopgateSubscriber implements EventSubscriberInterface
{
    public const SG_SESSION_KEY = 'sgWebView';

    public static function getSubscribedEvents(): array
    {
        return [
            HeaderPageletLoadedEvent::class => 'addIsShopgate'
        ];
    }

    public function addIsShopgate(HeaderPageletLoadedEvent $event): void
    {
        $hasSession = $event->getRequest()->hasSession();
        $sgAgent = strpos($event->getRequest()->headers->get('User-Agent'), 'libshopgate') !== false;
        $sgSession = $hasSession && $event->getRequest()->getSession()->get(self::SG_SESSION_KEY, 0);
        $sgCookie = $event->getRequest()->cookies->get(self::SG_SESSION_KEY, 0);

        $isShopgate = $sgAgent || $sgSession || $sgCookie;
        if (!$isShopgate) {
            return;
        }
        $event->getRequest()->getSession()->set(self::SG_SESSION_KEY, 1);
        $event->getRequest()->cookies->set('sgWebView', 1);
        $event->getPagelet()->addExtension('sg_connect_data', new ArrayStruct());
    }
}
