<?php

declare(strict_types=1);

/*
 * dateipolizei
 *
 * Date: 13.08.17 18:30
 */

namespace Ktomk\DateiPolizei\Config;

use Ktomk\DateiPolizei\Config;
use PHPUnit\Framework\TestCase;

/**
 * Class LoaderTest
 *
 * @covers \Ktomk\DateiPolizei\Config\Loader
 */
class LoaderTest extends TestCase
{
    function testCreation()
    {
        $loader = new Loader();
        $this->assertInstanceOf(Loader::class, $loader);
    }

    function testCreateConfig()
    {
        $loader = new Loader();
        $config = $loader->createConfig();
        $this->assertInstanceOf(Config::class, $config);
        $this->assertInternalType('array', $config->toArray());
    }
}
