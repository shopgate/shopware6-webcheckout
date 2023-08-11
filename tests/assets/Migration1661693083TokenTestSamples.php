<?php declare(strict_types=1);

namespace Shopgate\WebcheckoutSW6\System\Db\Migration;

use DateTime;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Exception;
use JsonException;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Migration\MigrationStep;
use Shopware\Core\Framework\Uuid\Uuid;
use Throwable;

class Migration1661693083TokenTestSamples extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1661693083;
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     * @throws JsonException
     * @throws Exception
     */
    public function update(Connection $connection): void
    {
        $payloads = [
            [
                'token' => 'WSLpAnT3vJsBt9R6rFpS9yKApkAwh9mu',
                'customerId' => '6c97534c2c0747f39e8751e43cb2b013'
            ],
            [
                'token' => 'dc03e666f44e4129a12f3b6ccdbaae27',
                'customerId' => null
            ]
        ];
        $storeId = $this->getIdOfSalesChannelViaTypeId($connection);
        array_map(function (array $payload) use ($storeId, $connection) {
            $this->createEntity($payload['token'], $storeId, $connection, $payload['customerId']);
        }, $payloads);
    }

    public function updateDestructive(Connection $connection): void
    {
        // empty
    }

    /**
     * get the id of the sales channel via the sales channel type id
     * @throws \Doctrine\DBAL\Exception
     */
    private function getIdOfSalesChannelViaTypeId(Connection $connection): string
    {
        $statement = $connection->prepare('SELECT id FROM sales_channel WHERE type_id = UNHEX(?)');

        return Uuid::fromBytesToHex($statement->executeQuery([Defaults::SALES_CHANNEL_TYPE_STOREFRONT])->fetchOne());
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     * @throws JsonException
     */
    private function createEntity(string $token, string $storeId, Connection $conn, ?string $customerId = null): void
    {
        try {
            $conn->createQueryBuilder()
                ->delete('sales_channel_api_context', 'a')
                ->where('a.token = :token')
                ->setParameter('token', $token)
                ->executeStatement();
        } catch (Throwable) {
            // empty
        }

        $qb = $conn->createQueryBuilder();
        $params = [
            'expired' => true,
            'currencyId' => Defaults::CURRENCY,
            'customerId' => $customerId,
            'languageId' => Defaults::LANGUAGE_SYSTEM,
            'permissions' => [],
            'billingAddressId' => null,
            'shippingAddressId' => null
        ];

        $qb
            ->insert('sales_channel_api_context')
            ->setValue('token', ':token')
            ->setValue('payload', ':payload')
            ->setValue('customer_id', ':customerId')
            ->setValue('updated_at', ':updatedAt')
            ->setValue('sales_channel_id', ':salesChannelId')
            ->setParameter('token', $token)
            ->setParameter('updatedAt', (new DateTime('2021-01-01'))->format(Defaults::STORAGE_DATE_TIME_FORMAT))
            ->setParameter('payload', json_encode($params, JSON_THROW_ON_ERROR))
            ->setParameter('customerId', $customerId ? Uuid::fromHexToBytes($customerId) : null)
            ->setParameter('salesChannelId', Uuid::fromHexToBytes($storeId));
        $qb->executeStatement();
    }
}
