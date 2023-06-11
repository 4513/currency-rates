<?php

declare(strict_types=1);

namespace MiBo\Currency\Rates\Exchangers;

use MiBo\Currency\Rates\Contracts\ExchangerInterface;
use MiBo\Currency\Rates\Exceptions\ExchangeRateNotAvailableException;
use MiBo\Currency\Rates\Traits\ExchangerHelper;

/**
 * Class CNB
 *
 *  The CNB is the central bank of the Czech Republic, the supervisor of the Czech financial market and the
 * Czech resolution authority. It is established under the Constitution of the Czech Republic and carries out
 * its activities in compliance with Act No. 6/1993 Coll., on the Czech National Bank, as amended and other
 * regulations. It is a legal entity under public law having its registered address in Prague. The CNB
 * performs its activities through its headquarters in Prague and regional offices in Ústí nad Labem,
 * Plzeň, České Budějovice, Hradec Králové, Brno and Ostrava. It manages its assets, including the international
 * reserves, with due diligence. Interventions in its activities are only permissible on the basis of a law.
 * The CNB is a part of the European System of Central Banks and contributes to the fulfilment of its objectives
 * and tasks. It is also a part of the European System of Financial Supervision and cooperates with the European
 * Systemic Risk Board and with European Supervisory Authorities.
 *
 * The supreme governing body of the CNB is the Bank Board, consisting of the CNB Governor, two Deputy
 * Governors and four other Bank Board members. All Bank Board members are appointed by the President
 * of the Czech Republic for a maximum of two six-year terms.
 *
 * Under Article 98 of the Constitution of the Czech Republic and in line with EU primary law, the primary
 * objective of the CNB is to maintain price stability. Achieving and maintaining price stability,
 * i.e. creating a low-inflation environment in the economy, is the central bank’s ongoing contribution
 * to the creation of conditions for sustainable economic growth. Central bank independence is a prerequisite
 * for effective monetary instruments conducive to price stability. In addition, the CNB fosters financial
 * stability and sees to the sound operation of the financial system in the Czech Republic. To this end, the CNB
 * sets macroprudential policy by identifying risks jeopardising the stability of the financial system and
 * contributing to its resilience. Without prejudice to its primary objective, the CNB also supports the general
 * economic policies of the Government and the general economic policies in the European Union.
 *
 *  In accordance with its primary objective, the CNB sets monetary policy. It also issues banknotes and
 * coins and manages and oversees the circulation of currency, the payment system and settlement between banks.
 * It also performs supervision of the banking sector, the capital market, the insurance industry, pension funds,
 * credit unions, electronic money institutions and bureaux de change. In order to undertake its tasks, the CNB
 * processes and generates statistical information. As a central bank the CNB provides banking services to the state
 * and the public sector. It maintains the accounts of persons and organisations connected to the state budget.
 * By agreement with the Ministry of Finance pursuant to the budgetary rules, the CNB conducts transactions
 * relating to government bond issues and financial market investments.
 *
 * @link https://www.cnb.cz/
 *
 * @package MiBo\Currency\Rates\Exchangers
 *
 * @author Michal Boris <michal.boris27@gmail.com>
 *
 * @since 0.1
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
class CNB implements ExchangerInterface
{
    use ExchangerHelper;

    // @phpcs:ignore
    protected const URL = "https://www.cnb.cz/cs/financni-trhy/devizovy-trh/kurzy-devizoveho-trhu/kurzy-devizoveho-trhu/denni_kurz.txt";

    /**
     * @inheritDoc
     */
    final public function getDefaultCurrencyCode(): string
    {
        return "CZK";
    }

    /**
     * @inheritDoc
     */
    protected function getFor(array $rates, string $currency, string $fromCurrency): float
    {
        $rate = ($rates[$currency]["amount"] ?? 1) / $rates[$currency]["rate"];

        if ($fromCurrency === $this->getDefaultCurrencyCode()) {
            return $rate;
        }

        return $rate / (($rates[$fromCurrency]["amount"] ?? 1) * $rates[$fromCurrency]["rate"]);
    }

    /**
     * @inheritDoc
     */
    public function getExchangeRate(): array
    {
        $content = file_get_contents(static::URL);

        if ($content === false) {
            throw new ExchangeRateNotAvailableException();
        }

        $lines = explode("\n", $content);
        $rates = [];

        foreach ($lines as $index => $line) {
            // First line is a date of publication and second line is a header. Ignoring.
            if ($index < 2) {
                continue;
            }

            [
                $country,
                $currency,
                $amount,
                $code,
                $rate,
            ] = explode("|", $line);

            $rates[$code] = [
                "amount" => (int) $amount,
                "rate"   => (float) $rate,
            ];
        }

        return $rates;
    }
}
