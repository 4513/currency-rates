<?php

declare(strict_types=1);

namespace MiBo\Currency\Rates\Tests;

use InvalidArgumentException;
use MiBo\Currency\Rates\Exceptions\ExchangeRateNotAvailableException;
use MiBo\Currency\Rates\Exchangers\CF;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use function assert;
use function is_string;

/**
 * Class CFTest
 *
 * @package MiBo\Currency\Rates\Tests
 *
 * @author Michal Boris <michal.boris27@gmail.com>
 *
 * @since 1.2.0
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
#[CoversClass(CF::class)]
#[Small]
final class CFTest extends ExchangerTestCase
{
    public function test(): void
    {
        // @phpcs:ignore SlevomatCodingStandard.Variables.DisallowSuperGlobalVariable.DisallowedSuperGlobalVariable
        $key = $_ENV['CF_API_KEY'] ?? '';
        assert(is_string($key));

        if ($key === '') {
            self::markTestSkipped("CF_API_KEY is not set");
        }

        $class = new CF($key);

        self::assertAvailableCountries(
            [
                "CZK",
                "EUR",
                "AUD",
                "BRL",
                "USD",
                "CAD",
                "SEK",
                "GBP",
                "PLN",
                "JPY",
            ],
            $class->getAvailableCurrencies()
        );

        self::assertRate(0.95, $class->getRateFor("EUR"));
        self::assertRate(23, $class->getRateFor("CZK"));
    }

    public function testMissingApiKey(): void
    {
        self::expectException(InvalidArgumentException::class);

        new CF("");
    }

    public function testInvalidApiKey(): void
    {
        self::expectException(ExchangeRateNotAvailableException::class);

        $class = new CF("invalid");

        $class->getAvailableCurrencies();
    }
}
