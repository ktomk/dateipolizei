<?php

/*
 * dateipolizei
 *
 * Date: 07.08.17 07:53
 */

namespace Ktomk\DateiPolizei\CliTest;

use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Ktomk\DateiPolizei\CliTest\PhpRunner
 * @covers \Ktomk\DateiPolizei\CliTest\XDebugHelper
 *
 */
class PhpRunnerTest extends TestCase
{
    function getRunner($cmd = ''): PhpRunner
    {

        $test = new TestCaseStub();
        $runner = new PhpRunner($test, $cmd);

        return $runner;
    }

    /**
     * Mocked runner which allows the run method to replace
     *
     * Useful to prevent i/o intensive operations in unit-tests
     *
     * @param callable $callback
     * @return \PHPUnit_Framework_MockObject_MockObject|PhpRunner
     */
    function getShallowRunner(callable $callback)
    {
        $method = 'run';
        $builder = $this->getMockBuilder(PhpRunner::class);
        $builder->setMethods([$method]);
        $builder->setConstructorArgs([$this, '']);
        $runner = $builder->getMock();

        $runDeflection = function (...$args) use ($callback, $runner) {
            $result = $callback(...$args);
            $r = new \ReflectionClass(PhpRunner::class);
            $p = $r->getProperty('last');
            $p->setAccessible(true);
            $p->setValue($runner, $result);

            return $result;
        };

        $runner
            ->expects($this->any())
            ->method($method)
            ->willReturnCallback($runDeflection);

        return $runner;
    }

    function testCreation()
    {
        $stub = $this->getMockBuilder(TestCase::class)->getMock();
        /** @var TestCase $stub */
        $runner = new PhpRunner($stub, 'echo');
        $this->assertInstanceOf(PhpRunner::class, $runner);
    }

    function testAssertStatusFail()
    {
        $runner = $this->getShallowRunner(function() {
            return [1, '', "error: you got me error\n", 'faux-command --error'];
        });

        $runner->assertStatus(1);
        try {
            $runner->assertOk();
            $this->fail('an expected exception was not thrown');
        } catch (ExpectationFailedException $ex) {
            $this->assertStringStartsWith("Status 0 expected, got", $ex->getMessage());
        }
    }

    function testAssertStatusOk()
    {
        $runner = $this->getRunner('');
        $runner->assertStatus(0);
        $runner->assertOk();
    }
}
