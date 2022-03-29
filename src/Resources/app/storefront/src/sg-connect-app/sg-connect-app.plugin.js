import Plugin from 'src/plugin-system/plugin.class';
import SGConnectEventManager from './event.manager';

export default class SgConnectAppPlugin extends Plugin {
    options = {
        controllerName: null,
        actionName: null,
        properties: null,
        env: null
    };

    init() {
        const {controllerName, actionName, properties, env} = this.options;
        this.eventManager = new SGConnectEventManager(controllerName, actionName, properties, env);
        this.initSGBridge();

        this.executeWithRetry(40, 3000, this.initShopgateApp.bind(this));
    }

    /**
     * Tries calling the given function and applies a retry mechanism for a given amount of time
     * and interval until the call succeeds or the time limit is exceeded.
     *
     * @param {number} intervalInMs
     * @param {number} maximumIntervalTimeInMs
     * @param {function} cb
     */
    executeWithRetry(intervalInMs, maximumIntervalTimeInMs, cb) {

        const startTimestampInMs = Date.now();
        // try before enabling the retry mechanism
        if (cb()) {
            return;
        }

        const interval = setInterval(
            function () {
                // stop retrying after some time
                if (startTimestampInMs + maximumIntervalTimeInMs <= Date.now()) {
                    clearInterval(interval);
                    return;
                }
                // clear interval upon success (no further retries)
                if (cb()) {
                    clearInterval(interval);
                }
            },
            intervalInMs
        );
    }

    /**
     * Inserts a vew scripts if the current context is right, so the browser can
     * communicate with the Shopgate App.
     *
     * @return {boolean} Returns false if the context is not the Shopgate App.
     */
    initShopgateApp() {
        /** @typedef {object} window.SGJavascriptBridge */
        if (!window.SGJavascriptBridge) {
            return false;
        }

        this.eventManager.registerDefaultEvents();
        this.eventManager.executeEvents();

        // close loading spinner after 3 seconds, in case something goes wrong
        setTimeout(function () {
            // show a warning message if it is still open
            window.SGAppConnector.closeLoadingSpinner();
        }, 3000);

        return true;
    }

    initSGBridge() {
        window.SGAppConnector = {
            /**
             * Stores response callbacks and pass through params for pipeline calls
             */
            pipelineResponseHandler: {},

            /**
             * Takes any type of variable and checks if the input is a function.
             *
             * @param {*|null} func
             * @return {boolean}
             */
            functionExists: function (func) {
                return (typeof func === 'function');
            },

            /**
             * Sends an array of app commands to the Shopgate app. The SGJavascriptBridge is required for this.
             *
             * @param {object[]} appCommands
             */
            sendAppCommands: function (appCommands) {
                const jsBridgeVersion = '12.0';
                if ('dispatchCommandsForVersion' in window.SGJavascriptBridge) {
                    window.SGJavascriptBridge.dispatchCommandsForVersion(appCommands, jsBridgeVersion);
                } else {
                    window.SGJavascriptBridge.dispatchCommandsStringForVersion(JSON.stringify(appCommands), jsBridgeVersion);
                }
            },

            /**
             * Sends an array of app commands to the Shopgate app. The SGJavascriptBridge is required for this.
             *
             * @param {object} appCommand
             */
            sendAppCommand: function (appCommand) {
                this.sendAppCommands([appCommand]);
            },

            /**
             * Creates a special app command to close the loading spinner.
             * A warning can be created, if the command is actually sent.
             */
            closeLoadingSpinner: function () {
                this.sendAppCommand({'c': 'onload'});
            },

            /**
             * Sends out a pipeline request and calls the given callback on response (if set).
             * A param can be passed through to the callback, when it's called.
             *
             * @param {string} pipelineName
             * @param {boolean} trusted
             * @param {*|null} data
             * @param {function|null} callback
             * @param {*|null} callbackParams
             */
            sendPipelineRequest: function (pipelineName, trusted, data, callback, callbackParams) {
                if (!data) {
                    data = {};
                }

                if (!callbackParams) {
                    callbackParams = null;
                }

                const appCommand = {
                    c: 'sendPipelineRequest',
                    p: {
                        serial: pipelineName,
                        name: pipelineName,
                        input: data
                    }
                };

                if (trusted) {
                    appCommand.p['type'] = 'trusted';
                }

                // set response callback if available
                this.pipelineResponseHandler[pipelineName] = {
                    callbackParams: callbackParams,
                    __call: function (err, output, callbackParams) {

                        if (!window.SGAppConnector.functionExists(callback)) {
                            console.log('## no callback registered for pipeline call: ' + pipelineName);
                            return;
                        }

                        console.log('## running response callback for pipeline call: ' + pipelineName);
                        return callback(err, output, callbackParams);
                    }
                };

                this.sendAppCommand(appCommand);
            },
        };

        // noinspection JSUnusedGlobalSymbols
        window.SGEvent = {
            __call: function (eventName, eventArguments) {

                console.log(
                    '# Received event ' + eventName
                );

                if (!eventArguments || !Array.isArray(eventArguments)) {
                    eventArguments = [];
                }

                if (SGEvent[eventName]) {
                    SGEvent[eventName].apply(SGEvent, eventArguments);
                }
            },

            /**
             * Pipeline response event handler.
             *
             * @param {object|null} err
             * @param {string} pipelineName Also known as "serial"
             * @param {object} output Pipeline response object in non-error case
             * @return {*|void}
             */
            pipelineResponse: function (err, pipelineName, output) {
                if (err) {
                    console.error('Called pipeline \'' + pipelineName + '\' resulted in an error: ' + JSON.stringify(err));
                }

                // call assigned response handler callback and pass through the callbackParams
                if (window.SGAppConnector.pipelineResponseHandler[pipelineName]) {
                    const responseHandler = window.SGAppConnector.pipelineResponseHandler[pipelineName];
                    return responseHandler.__call(err, output, responseHandler.callbackParams);
                }
            },

            /**
             * This event is called by the app to check if the lib is ready.
             *
             * @returns {boolean}
             */
            isDocumentReady: function () {
                return true;
            }
        };
    }
}
