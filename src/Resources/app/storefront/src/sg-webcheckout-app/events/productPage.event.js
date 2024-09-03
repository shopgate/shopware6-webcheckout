// <plugin root>/src/Storefront/Resources/app/storefront/src/utility/loading-indicator/page-loading-indicator.util.js
import BackdropUtil from 'src/utility/loading-indicator/page-loading-indicator.util';
import AbstractEvent from './abstract-event';

export default class ProductPageEvent extends AbstractEvent {
    supports(controllerName, actionName, properties) {
        return controllerName === 'product' && actionName === 'index';
    }

    /**
     * @param {Object} parameters
     */
    execute(parameters) {
        // triggers close inApp event on Add To Cart submit
        const plugin = window.PluginManager.getPluginInstanceFromElement(document.querySelector('[data-add-to-cart]'), 'AddToCart');
        plugin.$emitter.subscribe('beforeFormSubmit', () => BackdropUtil.create());
        plugin.$emitter.subscribe('openOffCanvasCart', this.closeBrowser.bind(this));
    }

    closeBrowser () {
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
