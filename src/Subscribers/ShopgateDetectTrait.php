<?php declare(strict_types=1);

namespace Shopgate\WebcheckoutSW6\Subscribers;

use Symfony\Component\HttpFoundation\Request;

trait ShopgateDetectTrait
{
    /**
     * Helper logic for developers to enable "mobile" call
     * without needing the SG App. More in the README.md
     */
    private function handleDevelopmentCookie(Request $request): bool
    {
        $sgCookie = $request->cookies->get(IsShopgateSubscriber::SG_SESSION_KEY, false);
        if ($sgCookie === '0' && $request->hasSession()) {
            $request->getSession()->remove(IsShopgateSubscriber::SG_SESSION_KEY);
        }
        return (bool)$sgCookie;
    }

    private function isShopgate(Request $request): bool
    {
        $sgCookie = $this->handleDevelopmentCookie($request);
        $sgAgent = strpos((string)$request->headers->get('User-Agent'), 'libshopgate') !== false;
        $hasSession = $request->hasSession();
        $sgSession = $hasSession && $request->getSession()->get(IsShopgateSubscriber::SG_SESSION_KEY, 0);

        return $sgAgent || $sgSession || $sgCookie;
    }
}
