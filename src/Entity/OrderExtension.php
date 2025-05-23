<?php declare(strict_types=1);

namespace Shopgate\WebcheckoutSW6\Entity;

use Shopware\Core\Checkout\Order\OrderDefinition as ShopwareOrderDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class OrderExtension extends EntityExtension
{
    final public const PROPERTY = 'shopgateWebcOrder';

    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            new OneToOneAssociationField(self::PROPERTY, 'id', 'sw_order_id', OrderDefinition::class, false)
        );
    }

    public function getEntityName(): string
    {
        return ShopwareOrderDefinition::ENTITY_NAME;
    }

    /**
     * Not needed starting SW 6.7
     */
    public function getDefinitionClass(): string
    {
        return ShopwareOrderDefinition::class;
    }
}
