<?php declare(strict_types=1);

namespace Shopgate\WebcheckoutSW6\System\Db\Rule;

use Shopgate\Shopware\Storefront\Controller\MainController;
use Shopgate\WebcheckoutSW6\Subscribers\SessionExtenderSubscriber;
use Shopgate\WebcheckoutSW6\Subscribers\ShopgateDetectTrait;
use Shopware\Core\Framework\Rule\Rule;
use Shopware\Core\Framework\Rule\RuleScope;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraints\Type;

class IsShopgateWebcheckoutRuleCondition extends Rule
{
    use ShopgateDetectTrait;

    public const UUID = 'b344814108424254b7c5147b2020f777';
    public const RULE_NAME = 'is_shopgate_webcheckout';
    /** @var bool */
    protected bool $isShopgateWebcheckout = false;
    private RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
        parent::__construct();
    }

    public function getName(): string
    {
        return self::RULE_NAME;
    }

    public function match(RuleScope $scope): bool
    {
        if (!$request = $this->requestStack->getCurrentRequest()) {
            return false;
        }
        $isShopgateWebcheckout = $this->isShopgate($request)
            || $request->headers->has(SessionExtenderSubscriber::SHOPGATE_CHECK);

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
        return [
            'isShopgateWebcheckout' => [new Type('bool')]
        ];
    }
}
