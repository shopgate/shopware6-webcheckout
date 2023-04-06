import AbstractEvent from './abstract-event';

export default class CloseBrowserEvent extends AbstractEvent {

    supports(controllerName, actionName, properties) {
        // Supposedly we should not hit this route (we redirect) unless it's rendered as a 404 page
        const isLoginRoute = controllerName === 'sgwebcheckout' && actionName === 'login';
        const isCartRoute = controllerName === 'checkout' && actionName === 'cartpage';

        return isLoginRoute || isCartRoute;
    }

    /**
     * @param {Object} parameters
     */
    execute(parameters) {
        window.SGAppConnector.sendAppCommand(
            {
                'c': 'broadcastEvent',
                'p': {
                    'event': 'closeInAppBrowser',
                    'parameters': [{'redirectTo': '/'}]
                }
            }
        );
    }
}
