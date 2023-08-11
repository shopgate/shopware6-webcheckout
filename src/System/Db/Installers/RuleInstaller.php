<?php declare(strict_types=1);

namespace Shopgate\WebcheckoutSW6\System\Db\Installers;

use Shopgate\WebcheckoutSW6\System\Db\Rule\IsShopgateWebcheckoutRuleGroup;

class RuleInstaller extends EntityInstaller
{
    protected array $entityInstallList = [IsShopgateWebcheckoutRuleGroup::class];

    protected string $entityName = 'rule';
}
