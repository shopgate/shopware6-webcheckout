// Import all necessary Storefront plugins
import SgWebcheckoutAppPlugin from './sg-webcheckout-app/sg-webcheckout-app.plugin';

// Register your plugin via the existing PluginManager
const PluginManager = window.PluginManager;
PluginManager.register('SgWebcheckoutAppPlugin', SgWebcheckoutAppPlugin, '[data-sg-webcheckout-app-plugin]');

// Necessary for the webpack hot module reloading server
if (module.hot) {
    module.hot.accept();
}
