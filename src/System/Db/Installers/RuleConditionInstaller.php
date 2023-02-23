<?php declare(strict_types=1);

namespace Shopgate\WebcheckoutSW6\System\Db\Installers;

use DateTime;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Shopgate\WebcheckoutSW6\System\Db\Rule\IsShopgateWebcheckoutRuleCondition;
use Shopgate\WebcheckoutSW6\System\Db\Rule\IsShopgateWebcheckoutRuleGroup;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Uuid\Uuid;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Throwable;

class RuleConditionInstaller
{
    /** @var ContainerInterface */
    private $connection;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->connection = $container->get(Connection::class);
    }

    public function install(): void
    {
        try {
            $this->installRuleCondition();
        } catch (Throwable $throwable) {
            // can throw when db already has rule condition
        }
    }

    /**
     * @throws DBALException
     */
    private function installRuleCondition(): void
    {
        $this->connection->insert('rule_condition', [
            'id' => Uuid::fromHexToBytes(IsShopgateWebcheckoutRuleCondition::UUID),
            'type' => IsShopgateWebcheckoutRuleCondition::RULE_NAME,
            'rule_id' => Uuid::fromHexToBytes(IsShopgateWebcheckoutRuleGroup::UUID),
            'parent_id' => null,
            'value' => '{"isShopgateWebcheckout": true}',
            'position' => 0,
            'custom_fields' => null,
            'created_at' => (new DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
            'updated_at' => null
        ]);
    }
}
