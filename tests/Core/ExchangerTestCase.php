<?php

declare(strict_types=1);

namespace MiBo\Currency\Rates\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Class ExchangerTestCase
 *
 * @package MiBo\Currency\Rates\Tests
 *
 * @author Michal Boris <michal.boris27@gmail.com>
 *
 * @since 0.1
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
abstract class ExchangerTestCase extends TestCase
{
    /**
     * @param array<string> $expected
     * @param array<string> $actual
     *
     * @return void
     */
    public static function assertAvailableCountries(array $expected, array $actual): void
    {
        foreach ($expected as $country) {
            self::assertContains($country, $actual);
        }
    }

    public static function assertRate(float $expected, float $actual, float $delta = 10): void
    {
        $min = $expected - ($expected / 100 * $delta);
        $max = $expected + ($expected / 100 * $delta);

        self::assertTrue(
            $actual >= $min && $actual <= $max,
            "Expected rate is not in range of $min - $max (actual $actual)"
        );
    }
}
