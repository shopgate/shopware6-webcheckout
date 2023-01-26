<?php declare(strict_types=1);

namespace Shopgate\WebcheckoutSW6\Entity;

use Shopware\Core\Checkout\Order\OrderDefinition as SWOrderDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class OrderExtension extends EntityExtension
{
    public const PROPERTY = 'shopgateWebcOrder';

    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            new OneToOneAssociationField(self::PROPERTY, 'id', 'sw_order_id', OrderDefinition::class, false)
        );
    }

    public function getDefinitionClass(): string
    {
        return SWOrderDefinition::class;
    }
}
