import '../core/component/is-shopgate-webcheckout';

/* global Shopware */
Shopware.Application.addServiceProviderDecorator('ruleConditionDataProviderService', (ruleConditionService) => {
    ruleConditionService.addCondition('is_shopgate_webcheckout', {
        component: 'is-shopgate-webcheckout',
        label: 'sg.condition.is-webcheckout',
        scopes: ['global'],
        group: 'misc'
    });

    return ruleConditionService;
});
