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
