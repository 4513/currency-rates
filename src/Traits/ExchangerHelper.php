<?php

declare(strict_types=1);

namespace MiBo\Currency\Rates\Traits;

use MiBo\Currencies\CurrencyInterface;
use MiBo\Currency\Rates\Exceptions\CurrencyNotAvailableException;

/**
 * Trait ExchangerHelper
 *
 * @package MiBo\Currency\Rates\Traits
 *
 * @author Michal Boris <michal.boris27@gmail.com>
 *
 * @since 0.1
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
trait ExchangerHelper
{
    /**
     * @inheritDoc
     */
    abstract public function getDefaultCurrencyCode(): string;

    /**
     * @inheritDoc
     */
    abstract public function getExchangeRate(): array;

    /**
     * @param array<string, array{amount?: int, rate: float}> $rates
     * @param string $currency
     * @param string $fromCurrency
     *
     * @return float
     */
    abstract protected function getFor(array $rates, string $currency, string $fromCurrency): float;

    /**
     * @inheritDoc
     */
    public function getRateFor(
        CurrencyInterface|string $currency,
        CurrencyInterface|string|null $fromCurrency = null
    ): float
    {
        $rates        = $this->getExchangeRate();
        $currency     = $currency instanceof CurrencyInterface ? $currency->getAlphabeticalCode() : $currency;
        $fromCurrency = $fromCurrency instanceof CurrencyInterface ?
            $fromCurrency->getAlphabeticalCode() :
            $fromCurrency;

        if ($currency === $fromCurrency || ($currency === $this->getDefaultCurrencyCode() && $fromCurrency === null)) {
            return 1;
        }

        if (!key_exists($currency, $rates)) {
            throw new CurrencyNotAvailableException();
        }

        if ($fromCurrency !== null && !key_exists($fromCurrency, $rates)) {
            throw new CurrencyNotAvailableException();
        }

        return $this->getFor($rates, $currency, $fromCurrency ?? $this->getDefaultCurrencyCode());
    }

    /**
     * @inheritDoc
     */
    public function getAvailableCurrencies(): array
    {
        return array_merge(array_keys($this->getExchangeRate()), [$this->getDefaultCurrencyCode()]);
    }
}
