import AbstractEvent from './abstract-event';

export default class LogoutEvent extends AbstractEvent {
    supports(controllerName, actionName) {
        return controllerName === 'sgconnect' && actionName === 'logout';
    }

    /**
     * @param {Object} parameters
     */
    execute(parameters) {
        window.SGAppConnector.sendPipelineRequest(
            'shopgate.user.logoutUser.v1',
            true,
            {},
            function () {
                window.SGAppConnector.sendAppCommands([
                    {
                        'c': 'broadcastEvent',
                        'p': {'event': 'userLoggedOut'}
                    },
                    {
                        'c': 'broadcastEvent',
                        'p': {
                            'event': 'closeInAppBrowser',
                            'parameters': [{'redirectTo': '/'}]
                        }
                    }
                ]);
            },
            []
        );
    }
}
