import '../core/component/is-shopgate';

/* global Shopware */
Shopware.Application.addServiceProviderDecorator('ruleConditionDataProviderService', (ruleConditionService) => {
    ruleConditionService.addCondition('is_shopgate_webcheckout', {
        component: 'is-shopgate-webcheckout',
        label: 'Is Shopgate Web Checkout',
        scopes: ['global']
    });

    return ruleConditionService;
});
