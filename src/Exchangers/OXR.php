<?php

declare(strict_types=1);

namespace MiBo\Currency\Rates\Exchangers;

use CompileError;
use InvalidArgumentException;
use MiBo\Currency\Rates\Contracts\ExchangerInterface;
use MiBo\Currency\Rates\Exceptions\ExchangeRateNotAvailableException;
use MiBo\Currency\Rates\Traits\ExchangerHelper;
use function assert;
use function is_array;
use function is_float;
use function is_string;

/**
 * Class OXR
 *
 *  Open Exchange Rates provides a simple, lightweight and portable JSON API with live and historical foreign
 * exchange (forex) rates for over 200 worldwide and digital currencies, via a simple and easy-to-integrate
 * API, in JSON format. Data are tracked and blended algorithmically from multiple reliable sources, ensuring
 * fair and unbiased consistency.
 *
 *  Exchange rates published through the Open Exchange Rates API are collected from multiple reliable
 * providers, blended together and served up in JSON format for everybody to use.
 *
 * @link https://openexchangerates.org/
 *
 * @license https://openexchangerates.org/services-agreement/
 *
 * @experimental This class is experimental because of not available testing env.
 *
 * @package MiBo\Currency\Rates\Exchangers
 *
 * @author Michal Boris <michal.boris27@gmail.com>
 *
 * @since 1.2
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 *
 * @codeCoverageIngoreStart
 */
final class OXR implements ExchangerInterface
{
    use ExchangerHelper;

    protected const URL = 'https://openexchangerates.org/api/latest.json?app_id={appId}&base={base}';

    /** @var non-empty-string */
    private string $applicationId;

    /** @var non-empty-string */
    private string $base;

    /**
     * @param string $appId
     * @param non-empty-string $base
     *
     * @return ($appId is non-empty-string ? void : never)
     */
    public function __construct(string $appId, string $base = 'USD')
    {
        if ($appId === '') {
            throw new InvalidArgumentException('The application ID must be a non-empty string.');
        }

        $this->applicationId = $appId;
        $this->base          = $base;
    }

    /**
     * @return non-empty-string
     */
    public function getDefaultCurrencyCode(): string
    {
        return $this->base;
    }

    /**
     * @inheritDoc
     */
    // @phpcs:ignore SlevomatCodingStandard.Complexity.Cognitive.ComplexityTooHigh
    public function getExchangeRates(): array
    {
        if (!extension_loaded('curl')) {
            throw new CompileError('The cURL extension is required to use OXR exchanger.');
        }

        $url = strtr(self::URL, [
            '{appId}' => $this->applicationId,
            '{base}'  => $this->base,
        ]);

        $curl = curl_init($url);

        if ($curl === false) {
            throw new ExchangeRateNotAvailableException('OXR not available: cURL init failed.');
        }

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

        $response = curl_exec($curl);
        $err      = curl_error($curl);
        $errno    = curl_errno($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        if ($httpCode >= 500) {
            throw new ExchangeRateNotAvailableException('OXR not available: HTTP ' . $httpCode);
        }

        if ($httpCode === 403) {
            throw new ExchangeRateNotAvailableException('OXR not available. Invalid API key.');
        }

        if ($errno !== 0) {
            throw new ExchangeRateNotAvailableException('OXR not available: ' . $err . "($errno)");
        }

        if (!is_string($response)) {
            throw new ExchangeRateNotAvailableException('OXR not available: Invalid response.');
        }

        $data = json_decode($response, true);

        if (!is_array($data) || !key_exists('rates', $data)) {
            throw new ExchangeRateNotAvailableException('OXR not available: Invalid response.');
        }

        $result = [];

        /**
         * @var non-empty-string $currency
         * 
         */
        foreach ($data['rates'] as $currency => $rate) {
            assert(is_float($rate));
            $result[$currency] = [
                'amount' => 1,
                'rate'   => $rate,
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
