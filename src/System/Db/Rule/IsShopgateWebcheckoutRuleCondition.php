<?php declare(strict_types=1);

namespace Shopgate\WebcheckoutSW6\System\Db\Rule;

use Shopgate\Shopware\Storefront\Controller\MainController;
use Shopgate\WebcheckoutSW6\Subscribers\IsShopgateSubscriber;
use Shopgate\WebcheckoutSW6\Subscribers\SessionExtenderSubscriber;
use Shopgate\WebcheckoutSW6\Subscribers\ShopgateDetectTrait;
use Shopware\Core\Framework\Rule\Rule;
use Shopware\Core\Framework\Rule\RuleScope;
use Shopware\Core\Framework\Struct\ArrayStruct;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

class IsShopgateWebcheckoutRuleCondition extends Rule
{
    use ShopgateDetectTrait;

    public final const UUID = 'b344814108424254b7c5147b2020f777';
    public const RULE_NAME = 'is_shopgate_webcheckout';
    protected bool $isShopgateWebcheckout = false;

    public function getName(): string
    {
        return self::RULE_NAME;
    }

    public function match(RuleScope $scope): bool
    {
        $isShopgateWebcheckout = defined(IsShopgateSubscriber::IS_WEBCHECKOUT) || defined(IsShopgateSubscriber::IS_API_CALL);
        // Checks if the shop administrator set the rule to "Is Shopgate Webcheckout => Yes"
        if ($this->isShopgateWebcheckout) {
            // Administrator wants the rule to match if a shopgate call.
            return $isShopgateWebcheckout;
        }
        // Shop administrator wants the rule to match if it's currently NOT a shopgate call.
        return !$isShopgateWebcheckout;
    }

    public function getConstraints(): array
    {
        return ['isShopgateWebcheckout' => [new Type('bool')]];
    }
}
