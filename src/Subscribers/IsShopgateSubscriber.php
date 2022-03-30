<?php declare(strict_types=1);

namespace Shopgate\ConnectSW6\Subscribers;

use Shopware\Core\Framework\Struct\ArrayStruct;
use Shopware\Storefront\Pagelet\Header\HeaderPageletLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;

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
        $sgCookie = $this->handleDevelopmentCookie($event->getRequest());
        $sgAgent = strpos((string) $event->getRequest()->headers->get('User-Agent'), 'libshopgate') !== false;
        $hasSession = $event->getRequest()->hasSession();
        $sgSession = $hasSession && $event->getRequest()->getSession()->get(self::SG_SESSION_KEY, 0);

        $isShopgate = $sgAgent || $sgSession || $sgCookie;
        if (!$isShopgate) {
            return;
        }
        $event->getRequest()->getSession()->set(self::SG_SESSION_KEY, 1);
        $event->getPagelet()->addExtension('sg_connect_data', new ArrayStruct());
    }

    /**
     * Helper logic for developers to enable "mobile" call
     * without needing the SG App. More in the README.md
     */
    private function handleDevelopmentCookie(Request $request): bool
    {
        $sgCookie = $request->cookies->get(self::SG_SESSION_KEY, false);
        if ($sgCookie === '0' && $request->hasSession()) {
            $request->getSession()->remove(self::SG_SESSION_KEY);
        }
        return (bool)$sgCookie;
    }
}
