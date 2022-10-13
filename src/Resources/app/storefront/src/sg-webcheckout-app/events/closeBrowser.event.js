import AbstractEvent from './abstract-event';

export default class CloseBrowserEvent extends AbstractEvent {
    /**
     * Supposedly we should not hit this route (we redirect) unless it's rendered as a 404 page
     */
    supports(controllerName, actionName) {
        return controllerName === 'sgwebcheckout' && actionName === 'login';
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
