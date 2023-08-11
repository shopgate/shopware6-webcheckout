<?php declare(strict_types=1);

namespace Shopgate\WebcheckoutSW6\Entity;

use Shopware\Core\Checkout\Order\OrderDefinition as SWOrderDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ReferenceVersionField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class OrderDefinition extends EntityDefinition
{
    final public const ENTITY_NAME = 'shopgate_webc_order';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return OrderCollection::class;
    }

    public function getEntityClass(): string
    {
        return OrderEntity::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required(), new ApiAware()),
            (new FkField('sw_order_id', 'shopwareOrderId', SWOrderDefinition::class))
                ->addFlags(new Required(), new ApiAware()),
            (new ReferenceVersionField(SWOrderDefinition::class, 'sw_order_version_id'))->addFlags(new Required()),
            (new StringField('user_agent', 'userAgent'))->addFlags(new Required(), new ApiAware()),
            (new OneToOneAssociationField(
                'order',
                'sw_order_id',
                'id',
                SWOrderDefinition::class,
                false
            ))->addFlags(new ApiAware()),
        ] + $this->defaultFields());
    }
}
