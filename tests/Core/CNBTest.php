<?php

declare(strict_types=1);

namespace MiBo\Currency\Rates\Tests;

use MiBo\Currency\Rates\Exchangers\CNB;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Medium;

/**
 * Class CNBTest
 *
 * @package MiBo\Currency\Rates\Tests
 *
 * @author Michal Boris <michal.boris27@gmail.com>
 *
 * @since 0.1
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
#[CoversClass(CNB::class)]
#[Medium]
final class CNBTest extends ExchangerTestCase
{
    public function test(): void
    {
        $class = new CNB();

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

        self::assertRate(0.04, $class->getRateFor("EUR"));
    }
}
