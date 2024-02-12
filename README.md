# Shopgate Shopware6 Webcheckout

## Production Checklist

### Install

#### Packagist install (recommended)

If this plugin is available on `packagist.org` to install simply add the composer package to the shopware's root
composer:

```shell
cd [shopware6 root folder]
composer require shopgate/webcheckout-shopware6
```

Afterward just increment the plugin version inside `root/composer.json`, and run `composer update` to get the latest
version.

#### Folder install

It can be installed manually by copying the plugin folder to `custom/plugins` directory. Like
so `custom/plugins/SgateWebcheckoutSW6`. Then you can install & enable like any other plugin. For this install method,
please make sure that there is a `vendor` directory inside the plugin folder as we have composer dependencies. You could
do it yourself by running:

```shell
cd [plugin folder]
# this is because we do not want to install shopware core files
composer remove shopware/core
```

#### Composer symlink (development)

Place the plugin in the `custom/plugins` folder. You can now link it to
composer by running this command in the root directory:

```shell
cd custom/plugins
git clone git@github.com:shopgate/shopware6-webcheckout.git
cd ../..
composer require shopgate/webcheckout-shopware6
```

### Enable & Activate

Install and activate the module via command line:

```shell
cd [shopware6 root folder]
bin/console plugin:refresh
bin/console plugin:install --activate SgateWebcheckoutSW6
```

You may install and activate via the Shopware administration panel instead, if you prefer.

### JS Compilation

For Shopware 6.5+ after installing & enabling the plugin you will need to re-compile JS.
If you have a GitHub installation, this should suffice: 

```shell
composer run build:js
bin/console theme:compile
```

For regular installations via Symfony2 Flex, zip or shopware-installer.phar.php:
```shell
bin/build-js.sh
```

### CSS Compilation

After every time you configure CSS in the `Admin > Extensions > Shopgate Webcheckout Config >
Custom CSS` you will need to recompile your theme (currently only via command line):

```shell
bin/console theme:compile
```

### Signature Secret

The App will be redirecting the customer to the checkout page or their account pages. In order to keep the login
information secure we will need to encrypt the calls the App makes. Therefore, we need a secure password of sorts
to encrypt this login data. Shopware 6 has a native security key that it uses for such cases, it's called
an `APP_SECRET`.

Firstly, you can check this value inside `[root]/.env` file. In development mode, it could be set to `APP_SECRET: 1`.
Which will not work as that's a lousy secret, and is meant to be changed.

If you do not see it there or want Shopware 6 to manage this data instead of leaving in a file.
Then you can do the following to check the secret value:

```shell
bin/console secrets:list --reveal
```

If no value is set, you can set a secure secret for your store by running this command in the console:

```shell
bin/console secrets:set APP_SECRET --random
```

Make sure you adhere to these minimum requirements when creating the secret key:

* the secret must be at least 8 characters in length;
* upper and/or lowercase letters are used;

### Configurations

- Currently, we do not support enabling `Settings  Login / Registration > Clear and delete cart on log out` setting. If
  you must keep it enabled, you could create a separate Sales Channel for the Shopgate App to use.

## Development

### Cookies

You can partially mimic a customer viewing our App by enabling a cookie in the browser `sgWebView`. This is useful if
you want to add CSS classes, and see how they show up without needing the App.

Open browser console and paste:

```javascript
// Enable view
document.cookie="sgWebView=1; expires=Thu, 18 Dec 2043 12:00:00 GMT; path=/; SameSite=None; Secure";
// Disable view
document.cookie="sgWebView=0; expires=Thu, 18 Dec 2043 12:00:00 GMT; path=/; SameSite=None; Secure";
```

### CSS

If you need to work with CSS quicker, you can enable SW6's storefront-watch & update our CSS file
`src/Resources/app/storefront/src/scss/base.scss`

#### Example CSS handles

These are just examples, look at the body classes to see what page has what handles

```css
/* All pages */
.is-sg-app {
    display: none;
}

/* Login page -> account/login */
body.is-ctl-auth.is-act-loginpage.is-sg-app {
    display: none;
}

/* Registration page -> account/register */
body.is-ctl-register.is-act-accountregisterpage.is-sg-app {
    display: none;
}
```

#### Special:
* `.is-sg-codebase-v1` - references the older Swift/Java App base, we know it by seeing Codebase:<11 in the Agent Header
* `.is-sg-codebase-v2` - references the newer React Native App base, we know it by seeing Codebase: 11+ in the Agent Header (e.g. `libshopgate/25.0 (Jagdwelt 24 11.0.2 Codebase:11.0.2)`)

### Session extending

If you are creating a custom extension. A header `shopgate-check: 1` needs to be provided with the regular SW6
Storefront API calls to extend the current customer session (in case it expired). This will make sure they don't get
logged out too often.
