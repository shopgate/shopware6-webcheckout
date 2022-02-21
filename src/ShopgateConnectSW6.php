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
            .test { padding: 0; };
        ";
    }
}
