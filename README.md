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
so `custom/plugins/ShopgateConnectSW6`.

#### Composer symlink

After placing it in the `static-plugins` folder you can now link it to composer by running this command in the root
directory:

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

### CSS class examples

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
