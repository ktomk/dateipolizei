<?php

declare(strict_types=1);

/*
 * dateipolizei
 *
 * Date: 15.08.17 21:50
 */

namespace Ktomk\DateiPolizei;

use PHPUnit\Framework\TestCase;

/**
 * Class DapoArgsTest
 */
class DapoArgsTest extends TestCase
{
    function testCreation()
    {
        $args = DapoArgs::create(__DIR__ . '/../../bin/dapo', 'dapo');
        $this->assertInstanceOf(DapoArgs::class, $args);
    }
}
