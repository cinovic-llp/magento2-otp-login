# Magento 2 OTP Login

This module is helps to login and register with the OTP.

## Magento 2 OTP Login Cinovic

- Step 1: Create a directory for the module `app/code/Cinovic`.
- Step 2: Move folder in to the Cinovic dir
- Step 3: Run below commands<br/>
    `php bin/magento setup:upgrade`<br/>
    `php bin/magento setup:static-content:deploy`<br/>
    `php bin/magento c:c`<br/>
    `php bin/magento c:f`
- Step 4: `composer require twilio/sdk` for Message.
- Step 5: Create account in twillio and get Sender id and Authorization key from twillio. (Ignore this step if you have already.)
- Step 6: Now configure the SDK `Sender ID`, `Authorization Key`, `Mobile Number`.

Now module is properly installed

# Support

Find us our support policy - https://store.cinovic.com/support.html/
