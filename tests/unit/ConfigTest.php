<?php

/*
 * dateipolizei
 *
 * Date: 11.08.17 16:15
 */

namespace Ktomk\DateiPolizei;

use Ktomk\DateiPolizei\Config\TestConfigHelper;
use PHPUnit\Framework\TestCase;

/**
 * Class ConfigStoreTest
 *
 * @covers \Ktomk\DateiPolizei\Config
 */
class ConfigTest extends TestCase
{
    function testCreation()
    {
        $config = new Config();
        $this->assertInstanceOf(Config::class, $config);
    }

    function testToArray()
    {
        $config = new Config();
        $this->assertInternalType('array', $config->toArray());
    }

    function testAccess()
    {
        $config = new Config();
        $this->assertInternalType('array', $config->access());
        $this->assertNull($config->access('space-time-continuum'));

        TestConfigHelper::setArray($config, ['space-time-continuum' => 1]);
        $this->assertNotNull($config->access('space-time-continuum'));
    }
}
