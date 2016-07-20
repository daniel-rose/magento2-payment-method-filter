[![Build Status](https://travis-ci.org/daniel-rose/magento2-payment-method-filter.svg?branch=master)](https://travis-ci.org/daniel-rose/magento2-payment-method-filter) [![Coverage Status](https://coveralls.io/repos/github/daniel-rose/magento2-payment-method-filter/badge.svg?branch=master)](https://coveralls.io/github/daniel-rose/magento2-payment-method-filter?branch=master)

# DR_PaymentMethodFilter
This module excludes payment methods from checkout by using filters.

## Description
This module allows to exclude active payment methods from checkout.

The following table shows the predefined filters which control the state of payment methods:

|Name|Description|
|---|---|
|GuestFilter|This filter is only for guest checkout. All disallowed payment methods for guests can be set at the system configuration.|
|CustomerFilter|Excludes active payment by the customer attribute "disallowed_payment_methods". Only available for customer checkout.|
|QuoteContentFilter|The product attribute "disallowed_payment_methods" controls which payment method can be removed from the active list. Available for both checkout types.|

## Installation

### Via composer
Open the command line and run the following commands
```
cd PATH_TO_MAGENTO_2_ROOT
composer require dr/payment-method-filter
```

### Via archive
* Download the ZIP-Archive
* Extract files
* Copy the extracted Files to PATH_TO_MAGENTO_2_ROOT/app/code/DR/PaymentMethodFilter
* Run the following Commands:
```
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy
```

## Support
If you have any issues with this extension, open an issue on GitHub (see URL above).

## Contribution
Any contributions are highly appreciated. The best way to contribute code is to open a [pull request on GitHub](https://help.github.com/articles/using-pull-requests/).

## Developer
Daniel Rose

* Xing: https://www.xing.com/profile/Daniel_Rose16

## Licence
[MIT License](https://opensource.org/licenses/MIT)

## Copyright
(c) 2016 Daniel Rose