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
 *
 * @covers \Ktomk\DateiPolizei\DapoArgs
 */
class DapoArgsTest extends TestCase
{
    function testCreation()
    {
        $args = DapoArgs::create(__DIR__ . '/../../bin/dapo', 'dapo');
        $this->assertInstanceOf(DapoArgs::class, $args);
    }

    function testUtilityName()
    {
        $bin = __DIR__ . '/../../bin/dapo';

        $utilityName = 'dapo';
        $args = DapoArgs::create($bin, $utilityName);
        $this->assertEquals($utilityName, $args->utility_name);
        $this->assertEquals($utilityName, $args->utility);

        $utility = "./path/to/" . $utilityName;
        $args = DapoArgs::create($bin, $utility);
        $this->assertEquals($utilityName, $args->utility_name);
        $this->assertEquals($utility, $args->utility);
    }

    function testGetCommand()
    {
        $args = DapoArgs::create(__DIR__ . '/../../bin/dapo', 'dapo', 'command');
        $args->getTokens()->rewind(); # initialize args parsing
        $this->assertEquals('command', $args->getCommand());
    }

    function testGetIgnore()
    {
        $args = DapoArgs::create(__DIR__ . '/../../bin/dapo', 'dapo');
        $this->assertNotNull($args->getIgnore());
    }
}
