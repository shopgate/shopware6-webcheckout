# Shopgate Shopware6 Connect

## Install

### Packagist install (recommended)

If this plugin is available on `packagist.org` to install simply add the composer package to the shopware's root
composer:

```shell
cd [shopware6 root folder]
composer require shopgate/connect-shopware6
```

Afterwards just increment the plugin version inside `root/composer.json`, and run `composer update` to get the latest
version.

### Folder install

It can be installed manually by copying the plugin folder to `custom/plugins` directory. Like
so `custom/plugins/ShopgateConnectSW6`. Then you can install & enable like any other plugin. For this install method,
please make sure that there is a `vendor` directory inside the plugin folder as we have composer dependencies. You could
do it yourself by running:

```shell
cd [plugin folder]
# this is because we do not want to install shopware core files
composer remove shopware/core
```

### Composer symlink

Adjust the location of the previous step & place the plugin in the `static-plugins` folder. You can now link it to
composer by running this command in the root directory:

```shell
cd [shopware6 root folder]

# this step is required only in case you do not already have this in the root composer.json specified
composer config repositories.sym '{"type": "path", "url": "custom/static-plugins/*", "options": {"symlink": true}}'
composer require shopgate/connect-shopware6:^0.1
```

## Enable & Activate

Install and activate the module:

```shell
cd [shopware6 root folder]
php bin/console plugin:refresh
php bin/console plugin:install --activate ShopgateConnectSW6
```

You may install and activate via the Shopware administration panel instead, if you prefer.

## Signature Secret

The JWT library we use imposes strict secret security as follows:

* the secret must be at least 8 characters in length;
* contain numbers;
* upper and lowercase letters;

You can set a secure secret for your store by running this command in the console:

```shell
bin/console secrets:set APP_SECRET
```

Check that a local value inside `.env` file is not rewriting you secret:

```shell
bin/console secrets:list
```

You can find out more on the Symfony [doc pages](https://symfony.com/doc/5.4/configuration/secrets.html).

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

#### Example css handles

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
