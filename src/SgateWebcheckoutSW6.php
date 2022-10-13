<?php declare(strict_types=1);

namespace Shopgate\WebcheckoutSW6;

use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;

if (file_exists(dirname(__DIR__) . '/vendor/autoload.php')) {
    require_once dirname(__DIR__) . '/vendor/autoload.php';
}

class SgateWebcheckoutSW6 extends Plugin
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

  .header-logo-main-link {
    pointer-events: none;
    cursor: default;
  }

  .header-search-col,
  .header-actions-col,
  .nav-main,
  .scroll-up-container,
  .footer-main {
    display: none;
  }

  .cookie-permission-container {
    display: none !important;
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
 * Account pages
 */
.is-ctl-accountprofile.is-act-index.is-sg-app,
.is-ctl-accountorder.is-act-orderoverview.is-sg-app {
  .order-table-header-context-menu-content-form {
    display: none;
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
