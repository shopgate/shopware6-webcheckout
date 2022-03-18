<?php

declare(strict_types=1);

namespace Shopgate\ConnectSW6;

use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class ShopgateConnectSW6 extends Plugin
{
    public function install(InstallContext $installContext): void
    {
        /** @var SystemConfigService $configBridge */
        $configBridge = $this->container->get(SystemConfigService::class);
        $configBridge->set(Config::KEY_CSS, $this->getDefaultCss());
    }

    private function getDefaultCss(): string
    {
        return "
/**
 * Global
 */
.is-sg-app .header-search-col,
.is-sg-app .header-actions-col,
.is-sg-app .nav-main,
.is-sg-app .top-bar-nav, {
  display: none;
}

/**
 * Login page
 */
.is-ctl-auth.is-act-loginpage.is-sg-app .col-lg-4,
.is-ctl-register.is-act-accountregisterpage.is-sg-app .col-lg-4 {
  display: none;
}";
    }
}
