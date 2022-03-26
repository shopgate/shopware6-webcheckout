<?php

declare(strict_types=1);

namespace Shopgate\ConnectSW6;

use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;

if (file_exists(dirname(__DIR__) . '/vendor/autoload.php')) {
    require_once dirname(__DIR__) . '/vendor/autoload.php';
}

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
.is-sg-app {

  padding-top: 4em;

  .header-search-col,
  .header-actions-col,
  .nav-main,
  .scroll-up-container {
    display: none;
  }

  .offcanvas {
    margin-top: 4em;
    padding-bottom: 4em;
  }

  .modal {
    top: 4em;
    padding-bottom: 4em;
  }
}

/**
 * Login page
 */
.is-ctl-auth.is-act-loginpage.is-sg-app .col-lg-4,
.is-ctl-register.is-act-accountregisterpage.is-sg-app .col-lg-4 {
  display: none;
}

/**
 * Checkout / Finish pages
 */
.is-ctl-checkout.is-act-finishpage.is-sg-app,
.is-ctl-checkout.is-act-confirmpage.is-sg-app {
  .header-minimal-back-to-shop {
    display: none;
  }

  .checkout {
    padding-top: 0;
  }

  .finish-content {
    margin-bottom: 3rem;
  }
}
";
    }
}
