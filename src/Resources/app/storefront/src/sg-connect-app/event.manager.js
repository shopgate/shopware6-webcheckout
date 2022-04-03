import PurchaseEvent from './events/purchase.event';
import LoginEvent from './events/login.event';
import LogoutEvent from './events/logout.event';

export default class SGConnectEventManager {

    constructor(controllerName, actionName, properties, env) {
        this.controllerName = controllerName;
        this.actionName = actionName;
        this.properties = properties;
        this.isDev = env === 'dev';
        /**
         * @type {AbstractEvent[]}
         */
        this.events = [];
    }

    registerDefaultEvents() {
        this.registerEvent(PurchaseEvent);
        this.registerEvent(LoginEvent);
        this.registerEvent(LogoutEvent);
    }

    /**
     * @param { AbstractEvent } Event
     */
    registerEvent(Event) {
        this.events.push(new Event(this.isDev));
    }

    executeEvents() {
        this.events.forEach(event => {
            if (!event.supports(this.controllerName, this.actionName)) {
                return;
            }
            if (!event.active) {
                return;
            }
            event.log('Executing event > ' + event.constructor.name);
            event.execute(this.properties);
        });
    }

    disableEvents() {
        this.events.forEach(event => {
            event.disable();
        });
    }
}
