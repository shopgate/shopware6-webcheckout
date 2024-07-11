// Register your plugin via the existing PluginManager
// see src/vendor/shopware/storefront/Resources/app/storefront/src/plugin-system/plugin.manager.js
window.PluginManager.register(
  'SgWebcheckoutAppPlugin',
  () => import('./sg-webcheckout-app/sg-webcheckout-app.plugin'),
  '[data-sg-webcheckout-app-plugin]'
);

// Necessary for the webpack hot module reloading server
if (module.hot) {
  module.hot.accept();
}
