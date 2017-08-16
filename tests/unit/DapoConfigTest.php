<?php

declare(strict_types=1);

/*
 * dateipolizei
 *
 * Date: 15.08.17 21:10
 */

namespace Ktomk\DateiPolizei;

use Ktomk\DateiPolizei\String\Matcher;
use PHPUnit\Framework\TestCase;

/**
 * Class DapoConfigTest
 *
 * @covers \Ktomk\DateiPolizei\DapoConfig
 */
class DapoConfigTest extends TestCase
{
    function testCreation()
    {
        $config = new DapoConfig();
        $this->assertInstanceOf(DapoConfig::class, $config);
    }

    function testGetIgnore()
    {
        $config = new DapoConfig();
        $matcher = $config->getIgnore();
        $this->assertInstanceOf(Matcher::class, $matcher);
    }
}
