<?php

declare(strict_types=1);

namespace MiBo\Currency\Rates\Tests;

use MiBo\Currency\Rates\Exchangers\BoE;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Medium;

/**
 * Class BoETest
 *
 * @package MiBo\Currency\Rates\Tests
 *
 * @author Michal Boris <michal.boris27@gmail.com>
 *
 * @since 1.1.0
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
#[CoversClass(BoE::class)]
#[Medium]
final class BoETest extends ExchangerTestCase
{
    public function test(): void
    {
        $class = new BoE();

        self::assertAvailableCountries(
            [
                "CZK",
                "EUR",
                "AUD",
                "USD",
                "CAD",
                "SEK",
                "GBP",
                "PLN",
                "JPY",
            ],
            $class->getAvailableCurrencies()
        );

        self::assertRate(1.16, $class->getRateFor("EUR"));
        self::assertRate(28.22, $class->getRateFor('CZK'));
    }
}
