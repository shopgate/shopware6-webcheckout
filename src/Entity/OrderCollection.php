<?php declare(strict_types=1);

namespace Shopgate\WebcheckoutSW6\Entity;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void             add(OrderEntity $entity)
 * @method void             set(string $key, OrderEntity $entity)
 * @method OrderEntity[]    getIterator()
 * @method OrderEntity[]    getElements()
 * @method OrderEntity|null get(string $key)
 * @method OrderEntity|null first()
 * @method OrderEntity|null last()
 *
 * @extends EntityCollection<OrderEntity>
 */
class OrderCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return OrderEntity::class;
    }
}
