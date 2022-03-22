// Import all necessary Storefront plugins
import SgConnectAppPlugin from './sg-connect-app/sg-connect-app.plugin';

// Register your plugin via the existing PluginManager
const PluginManager = window.PluginManager;
PluginManager.register('SgConnectAppPlugin', SgConnectAppPlugin, '[data-sg-connect-app-plugin]');

// Necessary for the webpack hot module reloading server
if (module.hot) {
    module.hot.accept();
}
