<?php

declare(strict_types=1);

/*
 * dateipolizei
 *
 * Date: 03.08.17 08:42
 */

namespace Ktomk\DateiPolizei\Report;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Ktomk\DateiPolizei\Report\ReportArray
 */
class ReportArrayTest extends TestCase
{
    function testCreation()
    {
        $report = new ReportArray(['count' => 0]);
        $this->assertInstanceOf(ReportArray::class, $report);
    }

    /**
     * @expectedException \BadMethodCallException
     */
    function testEmptyException()
    {
        new ReportArray([]);
    }

    function testCounting()
    {
        $report = new ReportArray(['count' => 0, 'named' => []]);
        $this->assertEquals(0, $report['count']);
        $report->count('count');
        $this->assertEquals(1, $report['count']);
        $report->count('count');
        $this->assertEquals(2, $report['count']);
    }

    function testNamedCounting()
    {
        $report = new ReportArray(['named' => []]);
        $this->assertEquals([], $report['named']);
        $report->countName('named', 'foo');
        $this->assertEquals(1, $report['named']['foo']);
        $report->countName('named', 'foo');
        $this->assertEquals(2, $report['named']['foo']);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    function testCounterValidation()
    {
        $report = new ReportArray(['named' => []]);
        $report->countName('foo', 'bar');
    }

    /**
     * set-checking, setting and un-setting throws exceptions
     */
    function testArrayAccessShielding()
    {
        $report = new ReportArray(['count' => 1]);
        try {
            /** @noinspection PhpExpressionResultUnusedInspection trigger exception */
            isset($report['foo']);
            $this->fail('an expected exception was not thrown');
        } catch (\BadMethodCallException $ex) {
            $this->addToAssertionCount(1);
        }

        try {
            $report['foo'] = true;
            $this->fail('an expected exception was not thrown');
        } catch (\BadMethodCallException $ex) {
            $this->addToAssertionCount(1);
        }

        try {
            unset($report['foo']);
            $this->fail('an expected exception was not thrown');
        } catch (\BadMethodCallException $ex) {
            $this->addToAssertionCount(1);
        }
    }

    function testArraySerialization()
    {
        $expected = ['count' => 1];
        $report = new ReportArray($expected);
        $this->assertEquals($expected, $report->jsonSerialize());
    }
}
