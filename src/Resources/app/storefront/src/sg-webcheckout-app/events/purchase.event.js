import AbstractEvent from './abstract-event';

export default class PurchaseEvent extends AbstractEvent {
    supports(controllerName, actionName, properties) {
        return controllerName === 'checkout' && actionName === 'finishpage';
    }

    /**
     * @param {Object} parameters
     * @param {Object} parameters.order
     */
    execute(parameters) {
        if (!parameters) {
            this.log('Checkout success, but order parameters are empty');
        }
        window.SGAppConnector.sendAppCommands(
            [
                {
                    'c': 'broadcastEvent',
                    'p': {'event': 'checkoutSuccess', parameters: [parameters]}
                },
                {
                    'c': 'setNavigationBarParams',
                    'p': {
                        'navigationBarParams': {
                            'leftButton': false,
                            'rightButton': true,
                            'rightButtonType': 'close',
                            'rightButtonCallback': 'SGAction.broadcastEvent({event: \'closeInAppBrowser\',\'parameters\': [{\'redirectTo\': \'/\'}]});'
                        }
                    }
                }
            ]
        );
    }
}
