<?php

declare(strict_types=1);

namespace MiBo\Currency\Rates\Exceptions;

use RuntimeException;

/**
 * Class ExchangeRateNotAvailableException
 *
 * @package MiBo\Currency\Rates\Exceptions
 *
 * @author Michal Boris <michal.boris27@gmail.com>
 *
 * @since 0.1
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
class ExchangeRateNotAvailableException extends RuntimeException implements CurrencyExchangerException
{
}
