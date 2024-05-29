<?php

declare(strict_types=1);

namespace MiBo\Currency\Rates\Exchangers;

use CompileError;
use InvalidArgumentException;
use MiBo\Currency\Rates\Contracts\ExchangerInterface;
use MiBo\Currency\Rates\Exceptions\ExchangeRateNotAvailableException;
use MiBo\Currency\Rates\Traits\ExchangerHelper;
use function assert;

/**
 * Class CF
 *
 *  CurrencyFreaks is a currency API created by JFreaks Software Solutions. They are a small team of great
 * people with crazy ideas. We are primarily located in Lahore, Pakistan. As a part of JFreaks, they have been
 * in the software development business for 3 years. Their expertise includes Data Analysis and location-based
 * application development.
 *
 * @link https://currencyfreaks.com/
 *
 * @license https://currencyfreaks.com/tos.html/
 *
 * @package MiBo\Currency\Rates\Exchangers
 *
 * @author Michal Boris <michal.boris27@gmail.com>
 *
 * @since 1.2.0
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class CF implements ExchangerInterface
{
    use ExchangerHelper;

    protected const URL = 'https://api.currencyfreaks.com/v2.0/rates/latest?apikey={apiKey}&base={base}';

    /** @var non-empty-string */
    private string $apiKey;

    /** @var non-empty-string */
    private string $base;

    /**
     * @param string $apiKey
     * @param non-empty-string $base
     *
     * @return ($apiKey is non-empty-string ? void : never)
     */
    public function __construct(string $apiKey, string $base = 'USD')
    {
        if ($apiKey === '') {
            throw new InvalidArgumentException('API key is required');
        }

        $this->apiKey = $apiKey;
        $this->base   = $base;
    }

    /**
     * @inheritDoc
     */
    public function getDefaultCurrencyCode(): string
    {
        return $this->base;
    }

    /**
     * @inheritDoc
     *
     * @return array<string, array{amount?: int, rate: float}>
     */
    // @phpcs:ignore SlevomatCodingStandard.Complexity.Cognitive.ComplexityTooHigh
    public function getExchangeRates(): array
    {
        if (!extension_loaded('curl')) {
            throw new CompileError('The cURL extension is required to use CF exchanger.');
        }

        $url = strtr(self::URL, [
            '{apiKey}' => $this->apiKey,
            '{base}'   => $this->base,
        ]);

        $curl = curl_init($url);

        if ($curl === false) {
            throw new ExchangeRateNotAvailableException('CF not available: cURL init failed.');
        }

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

        $response = curl_exec($curl);
        $err      = curl_error($curl);
        $errno    = curl_errno($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        if ($httpCode >= 500) {
            throw new ExchangeRateNotAvailableException('CF not available: HTTP ' . $httpCode);
        }

        if ($httpCode === 401) {
            throw new ExchangeRateNotAvailableException('CF not available. Invalid API key.');
        }

        if ($httpCode === 429) {
            throw new ExchangeRateNotAvailableException('CF not available. Limit exceeded.');
        }

        if ($errno !== 0) {
            throw new ExchangeRateNotAvailableException('CF not available: ' . $err . "($errno)");
        }

        if (!is_string($response)) {
            throw new ExchangeRateNotAvailableException('CF not available: Invalid response.');
        }

        $data = json_decode($response, true);

        if (!is_array($data) || !key_exists('rates', $data)) {
            throw new ExchangeRateNotAvailableException('CF not available: Invalid response.');
        }

        $result = [];

        /**
         * @var non-empty-string $currency
         */
        foreach ($data['rates'] as $currency => $rate) {
            assert(is_numeric($rate));

            $result[$currency] = [
                'amount' => 1,
                'rate'   => (float) $rate,
            ];
        }

        return $result;
    }

    /**
     * @inheritDoc
     *
     * @param array<string, array{amount?: int, rate: float}> $rates
     */
    protected function getFor(array $rates, string $currency, string $fromCurrency): float
    {
        $rate = $rates[$currency]["rate"];

        if ($fromCurrency === $this->getDefaultCurrencyCode()) {
            return $rate;
        }

        return $rates[$fromCurrency]["rate"] * $rate;
    }
}
