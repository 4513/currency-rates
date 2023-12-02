<?php

declare(strict_types=1);

namespace MiBo\Currency\Rates\Tests;

use MiBo\Currency\Rates\Exchangers\BoE;

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
 *
 * @coversDefaultClass \MiBo\Currency\Rates\Exchangers\BoE
 */
final class BoETest extends ExchangerTestCase
{
    /**
     * @medium
     *
     * @covers ::getExchangeRates
     * @covers ::getAvailableCurrencies
     * @covers ::getRateFor
     * @covers ::getFor
     * @covers ::getDefaultCurrencyCode
     * @covers ::composeUlr
     * @covers ::csvContentIntoArray
     *
     * @return void
     */
    public function test(): void
    {
        $class = new BoE();

        $this->assertAvailableCountries(
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

        $this->assertRate(1.16, $class->getRateFor("EUR"));
        $this->assertRate(28.22, $class->getRateFor('CZK'));
    }
}
