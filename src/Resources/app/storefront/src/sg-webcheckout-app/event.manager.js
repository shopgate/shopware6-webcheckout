import CloseBrowserEvent from './events/closeBrowser.event';
import LoginEvent from './events/login.event';
import PurchaseEvent from './events/purchase.event';

export default class SGWebcheckoutEventManager {

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
        this.registerEvent(CloseBrowserEvent);
        this.registerEvent(LoginEvent);
        this.registerEvent(PurchaseEvent);
    }

    /**
     * @param { AbstractEvent } Event
     */
    registerEvent(Event) {
        this.events.push(new Event(this.isDev));
    }

    executeEvents() {
        this.events.forEach(event => {
            if (!event.supports(this.controllerName, this.actionName) || !event.active) {
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
