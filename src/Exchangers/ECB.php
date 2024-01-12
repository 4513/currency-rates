<?php

declare(strict_types=1);

namespace MiBo\Currency\Rates\Exchangers;

use MiBo\Currency\Rates\Contracts\ExchangerInterface;
use MiBo\Currency\Rates\Exceptions\ExchangeRateNotAvailableException;
use MiBo\Currency\Rates\Traits\ExchangerHelper;
use XMLReader;

/**
 * Class ECB
 *
 *  The European Central Bank (ECB) is the prime component of the Eurosystem and the European System of
 * Central Banks (ESCB) as well as one of seven institutions of the European Union. It is one of the
 * world's most important central banks.
 *
 *  The ECB Governing Council makes monetary policy for the Eurozone and the European Union, administers
 * the foreign exchange reserves of EU member states, engages in foreign exchange operations, and defines
 * the intermediate monetary objectives and key interest rate of the EU. The ECB Executive Board enforces
 * the policies and decisions of the Governing Council, and may direct the national central banks when
 * doing so. The ECB has the exclusive right to authorise the issuance of euro banknotes. Member states can
 * issue euro coins, but the volume must be approved by the ECB beforehand. The bank also operates the TARGET2
 * payments system.
 *
 *  The ECB was established by the Treaty of Amsterdam in May 1999 with the purpose of guaranteeing and
 * maintaining price stability. On 1 December 2009, the Treaty of Lisbon became effective and the bank gained
 * the official status of an EU institution. When the ECB was created, it covered a Eurozone of eleven
 * members. Since then, Greece joined in January 2001, Slovenia in January 2007, Cyprus and Malta in January
 * 2008, Slovakia in January 2009, Estonia in January 2011, Latvia in January 2014, Lithuania in January 2015
 * and Croatia in January 2023. The current President of the ECB is Christine Lagarde. Seated in Frankfurt,
 * Germany, the bank formerly occupied the Eurotower prior to the construction of its new seat.
 *
 *  The ECB is directly governed by European Union law. Its capital stock, worth €11 billion, is owned by
 * all 27 central banks of the EU member states as shareholders.[5] The initial capital allocation key was
 * determined in 1998 on the basis of the states' population and GDP, but the capital key has been readjusted
 * since. Shares in the ECB are not transferable and cannot be used as collateral.
 *
 * @link https://www.ecb.europa.eu/
 *
 * @package MiBo\Currency\Rates\Exchangers
 *
 * @author Michal Boris <michal.boris27@gmail.com>
 *
 * @since 0.1
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
class ECB implements ExchangerInterface
{
    use ExchangerHelper;

    protected const URL = "https://www.ecb.europa.eu/stats/eurofxref/eurofxref-hist.xml";

    /**
     * @inheritDoc
     */
    final public function getDefaultCurrencyCode(): string
    {
        return "EUR";
    }

    /**
     * @inheritDoc
     */
    // @phpcs:ignore SlevomatCodingStandard.Complexity.Cognitive.ComplexityTooHigh
    public function getExchangeRates(): array
    {
        $rates     = [];
        $xmlReader = XMLReader::open(static::URL);

        if (is_bool($xmlReader)) {
            throw new ExchangeRateNotAvailableException();
        }

        // @phpstan-ignore-next-line XMLReader::$name problem? Seems like a bug in PHPStan.
        while ($xmlReader->read() && $xmlReader->name !== "Cube") {
            continue;
        }

        $xmlReader->read();

        while ($xmlReader->read()) {
            $DOMNode = $xmlReader->expand();

            if ($DOMNode === false) {
                throw new ExchangeRateNotAvailableException();
            }

            if ($DOMNode->attributes?->count() !== 2) {
                break;
            }

            /** @phpstan-var object{value: string}|null $currency */
            $currency = $DOMNode->attributes->item(0);

            /** @phpstan-var object{value: string}|null $rate */
            $rate                     = $DOMNode->attributes->item(1);
            $rates[$currency?->value] = ["rate" => (float) $rate?->value];
        }

        return $rates;
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

        return 1 / $rates[$fromCurrency]["rate"] * $rate;
    }
}
