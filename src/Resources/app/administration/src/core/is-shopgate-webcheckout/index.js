import template from './is-shopgate-webcheckout.html.twig';
/* global Shopware */
Shopware.Component.extend('is-shopgate-webcheckout', 'sw-condition-base', {
    template,
    computed: {
        selectValues() {
            return [
                {
                    label: this.$tc('global.sw-condition.condition.yes'),
                    value: true
                },
                {
                    label: this.$tc('global.sw-condition.condition.no'),
                    value: false
                }
            ];
        },
        isShopgateWebcheckout: {
            get() {
                this.ensureValueExist();

                if (this.condition.value.isShopgateWebcheckout === null) {
                    this.condition.value.isShopgateWebcheckout = false;
                }

                return this.condition.value.isShopgateWebcheckout;
            },
            set(isShopgateWebcheckout) {
                this.ensureValueExist();
                this.condition.value = {...this.condition.value, isShopgateWebcheckout: isShopgateWebcheckout};
            }
        }
    }
});
