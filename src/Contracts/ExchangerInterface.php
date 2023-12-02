<?php

declare(strict_types=1);

namespace MiBo\Currency\Rates\Contracts;

use MiBo\Currencies\CurrencyInterface;

/**
 * Interface ExchangerInterface
 *
 * @package MiBo\Currency\Rates\Contracts
 *
 * @author Michal Boris <michal.boris27@gmail.com>
 *
 * @since 0.1
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
interface ExchangerInterface
{
    /**
     * Currency code that is used as a default currency for the Exchanger. (ISO-4217)
     *
     * @return string 3 capitalized letters.
     */
    public function getDefaultCurrencyCode(): string;

    /**
     * Returns the rate for the provided currency.
     *
     *  If 'fromCurrency' is not provided, the default currency is used. If using string instead of
     * CurrencyInterface, the string is alphabetical code of the currency (ISO-4217).
     *
     *  The returned value is being expected as the rate for 1 unit of the 'fromCurrency', that is if the
     * default currency is 'EUR', and one wants a rate for 'USD', the returned value is '1.08' from the
     * equation where '1 EUR = 1.08 USD'.
     *
     * @param string|\MiBo\Currencies\CurrencyInterface $currency Currency for which the rate is returned.
     * @param string|\MiBo\Currencies\CurrencyInterface|null $fromCurrency Currency from which the rate is calculated.
     *
     * @return float Rate for the provided currency.
     */
    public function getRateFor(
        string|CurrencyInterface $currency,
        string|CurrencyInterface|null $fromCurrency = null
    ): float;

    /**
     * Provides full exchange rate for all currencies that are valid for the Exchanger.
     *
     *  Key of the returned array is the alphabetical code of currency, value is array where optional 'amount'
     * is the amount of the currency that is equal to 1 unit of the default currency and 'rate' is the rate
     * for the default currency.
     *
     * @return array<string, array{amount?: int, rate: float}> Exchange rate.
     */
    public function getExchangeRates(): array;

    /**
     * Lists all available currencies for the Exchanger.
     *
     * @return array<string> Available currencies.
     */
    public function getAvailableCurrencies(): array;
}
