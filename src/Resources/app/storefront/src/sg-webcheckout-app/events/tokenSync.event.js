import LoginEvent from './login.event'

/**
 * @typedef SGTokenParams
 * @property {string} token - customer sw-context token
 * @property {boolean} syncToken - whether token sync is needed
 */
export default class TokenSyncEvent extends LoginEvent {
    /**
     * @param {string} controllerName
     * @param {string} actionName
     * @param {any|SGTokenParams} properties
     */
    supports(controllerName, actionName, properties) {
        // login event handles logging the customer in & token sync
        const isLoginEvent = super.supports(controllerName, actionName, properties);
        // we sync token only if customer is logged in already
        const isSyncEvent = properties && properties.syncToken && properties.token;

        return isSyncEvent && !isLoginEvent;
    }

    /**
     * @param {SGTokenParams} parameters
     */
    execute(parameters) {
        window.SGAppConnector.sendPipelineRequest(
            'apite.user.setContextToken.v1',
            true,
            {
                'contextToken': parameters.token
            },
            function () {},
            []
        );
    }
}
