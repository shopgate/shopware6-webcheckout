<?php declare(strict_types=1);

namespace Shopgate\WebcheckoutSW6\Subscribers;

use Shopgate\WebcheckoutSW6\SgateWebcheckoutSW6;
use Symfony\Component\HttpFoundation\Request;

trait ShopgateDetectTrait
{
    public function isShopgateApiCall(Request $request): bool
    {
        return $request->headers->has(SgateWebcheckoutSW6::IS_SHOPGATE_CHECK) &&
            $request->headers->has('sw-context-token') &&
            $request->headers->has('sw-access-key');
    }

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

    /**
     * Native SG App should have a Codebase variable with
     * a version higher than 11
     *
     * @param Request $request
     * @return bool
     */
    private function isNativeBase(Request $request): bool {
        $regex = "/libshopgate.*?Codebase:(\d+\.\d+(\.\d+)?)/";
        preg_match($regex, (string) $request->headers->get('User-Agent'), $matches);

        return version_compare($matches[1] ?? '0.0.0', '11.0.0', '>=');
    }
}
