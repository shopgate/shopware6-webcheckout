<?php declare(strict_types=1);

namespace Shopgate\WebcheckoutSW6;

use Shopgate\WebcheckoutSW6\System\Db\Installers\RuleConditionInstaller;
use Shopgate\WebcheckoutSW6\System\Db\Installers\RuleInstaller;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;

if (file_exists(dirname(__DIR__) . '/vendor/autoload.php')) {
    require_once dirname(__DIR__) . '/vendor/autoload.php';
}

class SgateWebcheckoutSW6 extends Plugin
{
    final public const IS_SHOPGATE_CHECK = 'shopgate-check';

    public function install(InstallContext $installContext): void
    {
        /** @var SystemConfigService $configBridge */
        $configBridge = $this->container->get(SystemConfigService::class);
        $configBridge->set(Config::KEY_CSS, $this->getDefaultCss());

        (new RuleInstaller($this->container))->install($installContext);
        (new RuleConditionInstaller($this->container))->install();

        parent::install($installContext);
    }

    /**
     * Where you should look for Migration database scripts
     */
    public function getMigrationNamespace(): string
    {
        return 'Shopgate\WebcheckoutSW6\System\Db\Migration';
    }

    private function getDefaultCss(): string
    {
        return '
/**
 * Global
 */
.is-sg-app {

  .header-logo-main-link {
    pointer-events: none;
    cursor: default;
  }

  .header-search-col,
  .header-actions-col,
  .nav-main,
  .scroll-up-container,
  .footer-main,
  .footer-minimal {
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
 * Java/Swift based App specifics
 */
.is-sg-codebase-v1 {
  padding-top: 4em;
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
 * Product pages
 */
 .is-ctl-product.is-act-index.is-sg-app {
  .cms-breadcrumb.container,
  .product-detail-review-teaser {
    display: none;
  }

  .product-detail-description-text a,
  a.product-detail-manufacturer-link {
    pointer-events: none;
    cursor: default;
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
.is-ctl-register.is-act-checkoutregisterpage.is-sg-app,
.is-ctl-checkout.is-act-finishpage.is-sg-app,
.is-ctl-checkout.is-act-confirmpage.is-sg-app {

  .header-minimal-back-to-shop,
  .register-login-collapse-toogle {
    display: none;
  }

  .checkout {
    padding-top: 0;
  }

  .finish-content {
    margin-bottom: 3rem;
  }
}
';
    }
}
