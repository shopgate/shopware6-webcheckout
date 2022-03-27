export default class AbstractEvent {

    active = true;
    pluginName = 'Shopgate Connect Plugin';
    isDev = false;

    constructor(env) {
        this.isDev = env;
    }

    /* eslint-disable no-unused-vars */
    /**
     * @param {string} controllerName
     * @param {string} actionName
     * @returns {boolean}
     */
    supports(controllerName, actionName) {
        console.warn(`[${this.pluginName}] Method \'supports\' was not overridden by "` + this.constructor.name + '". Default return set to false.');
        return false;
    }

    /* eslint-enable no-unused-vars */

    execute() {
        console.warn(`[${this.pluginName}] Method \'execute\' was not overridden by "` + this.constructor.name + '".');
    }

    disable() {
        this.active = false;
    }

    log(message) {
        if (this.isDev) {
            console.warn(this.pluginName + ': ' + message);
        }
    }
}
