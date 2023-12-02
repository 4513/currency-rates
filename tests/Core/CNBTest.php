<?php

declare(strict_types=1);

namespace MiBo\Currency\Rates\Tests;

use MiBo\Currency\Rates\Exchangers\CNB;

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
 *
 * @coversDefaultClass \MiBo\Currency\Rates\Exchangers\CNB
 */
final class CNBTest extends ExchangerTestCase
{
    /**
     * @medium
     *
     * @covers ::getExchangeRates
     * @covers ::getAvailableCurrencies
     * @covers ::getRateFor
     * @covers ::getFor
     * @covers ::getDefaultCurrencyCode
     *
     * @return void
     */
    public function test(): void
    {
        $class = new CNB();

        $this->assertAvailableCountries(
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

        $this->assertRate(0.04, $class->getRateFor("EUR"));
    }
}
