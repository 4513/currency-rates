<?php

declare(strict_types=1);

namespace MiBo\Currency\Rates\Tests;

use MiBo\Currency\Rates\Exchangers\ECB;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Medium;

/**
 * Class ECBTest
 *
 * @package MiBo\Currency\Rates\Tests
 *
 * @author Michal Boris <michal.boris27@gmail.com>
 *
 * @since 0.1
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
#[CoversClass(ECB::class)]
#[Medium]
final class ECBTest extends ExchangerTestCase
{
    public function test(): void
    {
        $class = new ECB();

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

        self::assertRate(1, $class->getRateFor("EUR"));
        self::assertRate(25, $class->getRateFor("CZK"));
    }
}
