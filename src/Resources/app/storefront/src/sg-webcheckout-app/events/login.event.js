import AbstractEvent from './abstract-event';

export default class LoginEvent extends AbstractEvent {
    supports(controllerName, actionName) {
        return controllerName === 'sgwebcheckout' && actionName === 'registered';
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
        
        // fix SW6M-27: We don't get an event on iOS after the pipeline process completes.
        setTimeout(() => {
            window.SGAppConnector.sendAppCommands([
                {
                    'c': 'broadcastEvent',
                    'p': {'event': 'userLoggedIn'}
                }
            ]);
        }, 5000);      
    }
}
