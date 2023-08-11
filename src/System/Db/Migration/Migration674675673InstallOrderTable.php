<?php declare(strict_types=1);

namespace Shopgate\WebcheckoutSW6\System\Db\Migration;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration674675673InstallOrderTable extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 674675673;
    }

    /**
     * @throws Exception
     */
    public function update(Connection $connection): void
    {
        $sql = <<<SQL
        CREATE TABLE IF NOT EXISTS `shopgate_webc_order` (
          `id` BINARY(16) NOT NULL COMMENT 'Entity ID',
          `sw_order_id` BINARY(16) NOT NULL COMMENT 'Shopware Order Id',
          `sw_order_version_id` BINARY(16) NOT NULL COMMENT 'Shopware Order Version Ref',
          `user_agent` VARCHAR(255) DEFAULT NULL COMMENT 'User Agent',
          `created_at` DATETIME(3) NOT NULL,
          `updated_at` DATETIME(3) NULL,
          UNIQUE `uniq.id` (`id`),
          KEY `SHOPGATE_WEBC_ORDER_ORDER_ID` (`sw_order_id`),
          CONSTRAINT `SHOPGATE_WEBC_ORDER_ORDER_ID_SALES_ORDER_ENTITY_ID` FOREIGN KEY (`sw_order_id`, `sw_order_version_id`) REFERENCES `order` (`id`, `version_id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Shopgate WebCheckout Orders' COLLATE=utf8mb4_unicode_ci;
SQL;
        $connection->executeStatement($sql);
    }

    public function updateDestructive(Connection $connection): void
    {
        // none
    }
}
