import AbstractEvent from './abstract-event';

export default class LoginEvent extends AbstractEvent {
    supports(controllerName, actionName, properties) {
        const isRegistrationPage = controllerName === 'sgwebcheckout' && actionName === 'registered';
        const isCheckoutPage = controllerName === 'checkout' && actionName === 'confirmpage';

        // login user after registration
        return isRegistrationPage || (!properties.guest && isCheckoutPage && properties.referer.includes('checkout/register'));
    }

    /**
     * @typedef SGTokenParams
     * @property {string} token - customer sw-context token
     */
    /**
     * @param {SGTokenParams} parameters
     */
    execute(parameters) {
        if (!parameters) {
            this.log('Login success, but no context token is passed from twig template');
        }
        window.SGAppConnector.sendPipelineRequest(
            'shopgate.user.loginUser.v1',
            true,
            {
                'strategy': 'auth_code',
                'parameters': {'code': parameters.token}
            },
            function () {
                window.SGAppConnector.sendAppCommands([
                    {
                        'c': 'broadcastEvent',
                        'p': {'event': 'userLoggedIn'}
                    }
                ]);
            },
            []
        );
    }
}
