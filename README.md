# Currency Rates

Online currency rate provider.  

The library comes with `\MiBo\Currency\Rates\ExchangerInterface` interface, which provides the following methods:
* `getDefaultCurrency()` - returns default currency code of the Exchanger (e.g. USD)
* `getRateFor()` - returns rate for the given currency code. Comparing the default currency of the Exchanger
  if no 'fromCurrency' parameter is provided.
* `getExchangeRate()` - list of all exchange rates of the Exchanger. The result is an array where a key is a currency
  code and a value of 'rate' sub key is a rate for the given currency code. Comparing the default currency of the Exchanger.
  If 'amount' sub key is present, the rate is calculated for the given amount.
* `getAvailableCurrencies()` - returns list of available currencies of the Exchanger. The result is an array of
  currency codes.

The library comes with a few implementations of the Exchangers:
* ECB - European Central Bank
* CNB - Czech National Bank
* *more to come...*

All implementations load the rates from publicly available resources. All resources are located on official
websites of the banks, thus the rates are trustworthy and up to date.

---
### Installation
```bash
composer require mibo/currency-rates
```

### Usage
```php
$price = 100; // EUR
$exchanger = new \MiBo\Currency\Rates\Exchangers\ECB();
$newPrice = $exchanger->getRateFor('USD') * $price; // USD
```

---
### Future of the library
The library will contain more implementations of the Exchangers. The goal is to provide a simple way to get
currency rates from various sources. The main focus is on the banks that are globally recognized.
