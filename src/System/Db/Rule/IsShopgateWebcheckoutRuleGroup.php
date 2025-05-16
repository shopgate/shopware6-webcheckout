<?php declare(strict_types=1);

namespace Shopgate\WebcheckoutSW6\System\Db\Rule;

use Shopgate\Shopware\System\Db\EntityChecker;
use Shopgate\WebcheckoutSW6\System\Db\ClassCastInterface;
use Shopware\Core\Content\Rule\RuleEntity;

/**
 * Backwards compatibility with 6.6
 */
$hasType = EntityChecker::checkPropertyHasType();
if (!$hasType) {
    class IsShopgateWebcheckoutRuleGroup extends RuleEntity implements ClassCastInterface
    {
        final public const UUID = '7d24818ee04546d797cb6fc1a604a777';

        protected $id = self::UUID;
        protected $name = 'Is Shopgate Webcheckout';
        protected $description = 'Check if the call is from Shopgate App';
        protected $priority = 80;

        public function toArray(): array
        {
            return [
                'id' => $this->id,
                'name' => $this->name,
                'description' => $this->description,
                'priority' => $this->priority,
                'conditions' => $this->conditions
            ];
        }
    }
} else {
    class IsShopgateWebcheckoutRuleGroup extends RuleEntity implements ClassCastInterface
    {
        final public const UUID = '7d24818ee04546d797cb6fc1a604a777';

        protected string $id = self::UUID;
        protected string $name = 'Is Shopgate Webcheckout';
        protected ?string $description = 'Check if the call is from Shopgate App';
        protected int $priority = 80;

        public function toArray(): array
        {
            return [
                'id' => $this->id,
                'name' => $this->name,
                'description' => $this->description,
                'priority' => $this->priority,
                'conditions' => $this->conditions
            ];
        }
    }
}

