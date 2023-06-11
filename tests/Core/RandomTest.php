<?php

declare(strict_types = 1);

namespace MiBo\Currency\Rates\Tests;

use MiBo\Currency\Rates\Exchangers\ECB;
use PHPUnit\Framework\TestCase;

/**
 * Class RandomTest
 *
 * @package MiBo\Currency\Rates\Tests
 *
 * @author Michal Boris <michal.boris27@gmail.com>
 *
 * @since x.x
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
class RandomTest extends TestCase
{
    /**
     * @small
     *
     * @coversNothing
     *
     * @return void
     */
    public function test(): void
    {
        $a = new ECB();
        $a->getExchangeRate();
        $this->assertTrue(true);
    }
}
