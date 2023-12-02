<?php

declare(strict_types=1);

namespace MiBo\Currency\Rates\Exchangers;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use MiBo\Currency\Rates\Contracts\ExchangerInterface;
use MiBo\Currency\Rates\Exceptions\ExchangeRateNotAvailableException;
use MiBo\Currency\Rates\Traits\ExchangerHelper;
use function count;

/**
 * Class BoE
 *
 *  The Bank of England is the central bank of the United Kingdom and the model on which most modern central
 * banks have been based. Established in 1694 to act as the English Government's banker, and still one of
 * the bankers for the Government of the United Kingdom, it is the world's eighth-oldest bank. It was
 * privately owned by stockholders from its foundation in 1694 until it was nationalised in 1946 by
 * the Attlee ministry.
 *
 *  The bank became an independent public organisation in 1998, wholly owned by the Treasury Solicitor on
 * behalf of the government, with a mandate to support the economic policies of the government of the day,
 * but independence in maintaining price stability.
 *
 *  The bank is one of eight banks authorised to issue banknotes in the United Kingdom, has a monopoly on
 * the issue of banknotes in England and Wales, and regulates the issuance of banknotes by commercial banks
 * in Scotland and Northern Ireland.
 *
 * @link https://www.bankofengland.co.uk/
 *
 * @license https://www.nationalarchives.gov.uk/doc/open-government-licence/version/3/
 *
 * @package MiBo\Currency\Rates\Exchangers
 *
 * @author Michal Boris <michal.boris27@gmail.com>
 *
 * @since 1.1.0
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
class BoE implements ExchangerInterface
{
    use ExchangerHelper;

    private const URL = 'https://www.bankofengland.co.uk/boeapps/database/_iadb-fromshowcolumns.asp?csv.y=yes';
    private const SERIES = [
        'XUDLADS'  => 'AUD',
        'XUDLBK25' => 'CZK',
        'XUDLBK33' => 'HUF',
        'XUDLBK47' => 'PLN',
        'XUDLBK78' => 'ILS',
        'XUDLBK83' => 'MYR',
        'XUDLBK87' => 'THB',
        'XUDLBK89' => 'CNY',
        'XUDLBK93' => 'KRW',
        'XUDLBK95' => 'TRY',
        'XUDLBK97' => 'INR',
        'XUDLCDS'  => 'CAD',
        'XUDLDKS'  => 'DKK',
        'XUDLERS'  => 'EUR',
        'XUDLHDS'  => 'HKD',
        'XUDLJYS'  => 'JPY',
        'XUDLNDS'  => 'NZD',
        'XUDLNKS'  => 'NOK',
        'XUDLSFS'  => 'CHF',
        'XUDLSGS'  => 'SGD',
        'XUDLSKS'  => 'SEK',
        'XUDLSRS'  => 'SAR',
        'XUDLTWS'  => 'TWD',
        'XUDLUSS'  => 'USD',
        'XUDLZRS'  => 'ZAR',
    ];

    /**
     * @inheritDoc
     */
    final public function getDefaultCurrencyCode(): string
    {
        return 'GBP';
    }

    /**
     * @return non-empty-string
     */
    final protected function composeUlr(): string
    {
        /** @phpstan-var \Carbon\Carbon $currentTime */
        $currentTime = Carbon::now()->isWeekend() ? Carbon::now()->previous(CarbonInterface::FRIDAY) : Carbon::now();

        /** @phpstan-var \Carbon\Carbon $timeFrom */
        $timeFrom   = $currentTime->copy()->subDays(3);
        $queries    = [
            'csv.x'      => 'yes',
            'CSVF'       => 'CN',
            'DAT'        => 'RNG',
            'FD'         => $timeFrom->day,
            'Filter'     => 'N',
            'FM'         => $timeFrom->format('M'),
            'FNY'        => '',
            'FromSeries' => 1,
            'FY'         => $timeFrom->year,
            'TD'         => $currentTime->day,
            'TM'         => $currentTime->format('M'),
            'ToSeries'   => 50,
            'Travel'     => 'NIxIRxSUx',
            'TY'         => $currentTime->year,
        ];
        $currencies = [
            'C=EC3',
            'C=DS7',
            'C=5LA',
            'C=5OW',
            'C=IN7',
            'C=IN8',
            'C=INA',
            'C=INB',
            'C=INC',
            'C=IND',
            'C=INE',
            'C=ECL',
            'C=ECH',
            'C=C8J',
            'C=ECN',
            'C=C8N',
            'C=ECO',
            'C=EC6',
            'C=ECU',
            'C=ECQ',
            'C=ECC',
            'C=ECZ',
            'C=ECD',
            'C=C8P',
            'C=ECE',
        ];

        return self::URL . '&' . http_build_query($queries) . '&' . implode('&', $currencies);
    }

    /**
     * @inheritDoc
     */
    public function getExchangeRates(): array
    {
        $content = file_get_contents($this->composeUlr());

        if ($content === false) {
            throw new ExchangeRateNotAvailableException();
        }

        return $this->csvContentIntoArray($content);
    }

    /**
     * @inheritDoc
     */
    public function getAvailableCurrencies(): array
    {
        return array_merge(array_values(self::SERIES), [$this->getDefaultCurrencyCode()]);
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

    /**
     * @param string $data
     *
     * @return array<string, array{amount: int<1, 1>, rate: float}>
     */
    private function csvContentIntoArray(string $data): array
    {
        $rows = explode("\n", $data);

        unset($rows[0]);

        /** @phpstan-var array{0: string, 1: key-of<self::SERIES>, 2: numeric-string} $rows */
        $rows = array_map('str_getcsv', $rows);
        // @phpstan-ignore-next-line
        $rows = array_filter($rows, static fn (array $row): bool => count($row) === 3);
        $rows = array_map(
            // @phpstan-ignore-next-line
            static fn (array $row): array => [
                'currency' => self::SERIES[$row[1]],
                'date'     => Carbon::createFromFormat('d M Y', $row[0]),
                'value'    => (float) $row[2],
            ],
            $rows
        );

        /** @phpstan-var \Carbon\Carbon|null $latestDate */
        $latestDate = null;

        /** @var array{date: \Carbon\Carbon, value: float, currency: string} $row */
        foreach ($rows as $row) {
            $latestDate ??= $row['date'];

            if ($latestDate->isAfter($row['date']) || $latestDate->isSameDay($row['date'])) {
                break;
            }
        }

        /** @phpstan-var array<array{date: \Carbon\Carbon, currency: string, value: float}> $rows */
        array_filter($rows, static fn (array $row): bool => $row['date']->isSameDay($latestDate));

        $result = [];

        foreach ($rows as $row) {
            $result[$row['currency']] = [
                'amount' => 1,
                'rate'   => $row['value'],
            ];
        }

        return $result;
    }
}
