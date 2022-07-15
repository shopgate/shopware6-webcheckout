import AbstractEvent from './abstract-event';

export default class PurchaseEvent extends AbstractEvent {
    supports(controllerName, actionName) {
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
                    'p': {'event': 'checkoutSuccess', parameters}
                },
                {
                    'c': 'setNavigationBarParams',
                    'p': {
                        'navigationBarParams': {
                            'leftButton': true,
                            'rightButton': true,
                            'leftButtonType': 'close',
                            'rightButtonType': 'close',
                            'leftButtonCallback': 'SGAction.broadcastEvent({event: \'closeInAppBrowser\',\'parameters\': [{\'redirectTo\': \'/\'}]});',
                            'rightButtonCallback': 'SGAction.broadcastEvent({event: \'closeInAppBrowser\',\'parameters\': [{\'redirectTo\': \'/\'}]});'
                        }
                    }
                }
            ]
        );
    }
}
