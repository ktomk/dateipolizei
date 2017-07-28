<?php

/*
 * dateipolizei
 */

namespace Ktomk\DateiPolizei\CliTest;

use PHPUnit\Framework\TestCase;

/**
 * Class SmokeTest
 *
 * This test-case is just to get the rubber on the road
 *
 * @covers \Ktomk\DateiPolizei\CliTest\PhpRunner
 * @covers \Ktomk\DateiPolizei\CliTest\XDebugHelper
 */
class SmokeTest extends TestCase
{
    public function testCreation()
    {
        $runner = new PhpRunner($this, 'bin/dapo');

        $runner->assertOk(['--help'], 'help works');
        $runner->assertStatus(
            129,
            ['--invalid'],
            'invalid option gives 129'
        );

        $runner->assertOk(['report', 'cli'], 'report works');
    }
}
