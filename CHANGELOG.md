# 2.3.0

- added a guest context when logging in to prevent token changes
- removed logout functionality to prevent token changes
- removed v2.2.2 re-sync with App if token changed (unneeded complication)

# 2.2.4

- fixed issue with observer assuming request is not null

# 2.2.3

- added compiled frontend JS file
- removed compatibility with 6.6.0.0+

# 2.2.2

- fixed inApp session drops after visiting checkout/profile pages for SW 6.5.8.9+, 6.6.1.1+
- fixed issue with double JS load on storefront
- removed compiled frontend JS file due to lack of compatibility between 6.5 & 6.6

# 2.2.1

- fixes for admin JS & vue3

# 2.2.0

- added support to Shopware 6.6.0.1

# 2.1.0

- added CSS body tag that differentiates between R.Native (`.is-sg-codebase-v2`) & old Swift/Java based App (`.is-sg-codebase-v1`) 

# 2.0.2

- Fixed checkout order conversion data feed 

# 2.0.1

- Added dev logging to JS for easier debugging
- Recompiled javascript
- Changed readme for after install recompilation process

# 2.0.0

- Added support for Shopware 6.5.2
- Removed support for Shopware 6.4
- Changed dependencies for PHP8 compatibility

# 1.4.0

- Added support for guest checkout
- Added identifying the order as Shopgate's
- Fixed issues with internal globals being already defined

# 1.3.0

- Added Cart Rule Condition for Webcheckout only Discounts
- Fixed token extension for `getContext` store API call

# 1.2.0

- Added order table to save `User-Agent` when an order is created via Web Checkout

# 1.1.1

- Changed condition for webpack hot module reloading to avoid compatibility issues on production environment

# 1.1.0

- Added a close inApp browser call when we hit the `cart` page
- Changed how meta tag is injected to support Safari inApp browser on IOS devices
- Removed previously added close inApp browser call after registration

# 1.0.0

- Changed name from `Connect` to `Webcheckout` in preparation for public release

# 0.3.2

- Removed entity repository references as SW6 `SwagMarkets` is not ready for 6.5 yet

# 0.3.1

- Fixed iOS inApp browser closing after 5 seconds (during registration) if no response is received from App

# 0.3.0

- Added ability to extend expired sessions when default Storefront API calls are made
- Changed validation to accept secret without numbers

# 0.2.0

- Added `close` button to success purchase page
- Removed `back` button from success purchase page
- Removed footer & cookie drawer via CSS

# 0.1.2

- Added Shopgate logo

# 0.1.1

- Fixed validation to allow only lower or upper case characters in the secret

# 0.1.0

- Added storefront API token generating endpoint for login purposes
- Added storefront controller for logging in, logging out, registration & registered
- Added JS infrastructure to handle controller/action hits
- Added ability to identify Shopgate App & fake App session for developer work
- Added example CSS to installation routine
- Added postman integration tests
