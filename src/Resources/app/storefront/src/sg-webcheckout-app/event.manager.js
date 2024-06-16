import CloseBrowserEvent from './events/closeBrowser.event';
import LoginEvent from './events/login.event';
import PurchaseEvent from './events/purchase.event';
import TokenSyncEvent from './events/tokenSync.event';

export default class SGWebcheckoutEventManager {
    /**
     * @param {string} controllerName
     * @param {string} actionName
     * @param {?SGWebcheckout.properties} properties
     * @param {'production'|'dev'|null} env
     */
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
        this.registerEvent(TokenSyncEvent);
    }

    /**
     * @param { AbstractEvent } Event
     */
    registerEvent(Event) {
        this.events.push(new Event(this.isDev));
    }

    executeEvents() {
        this.events.forEach(event => {
            if (!event.supports(this.controllerName, this.actionName, this.properties) || !event.active) {
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
