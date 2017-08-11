<?php

declare(strict_types=1);

/*
 * dateipolizei
 *
 * Date: 04.08.17 08:46
 */

namespace Ktomk\DateiPolizei\Report;

use Ktomk\DateiPolizei\Fs\INode;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Ktomk\DateiPolizei\Report\ExtensionReport
 */
class ExtensionReportTest extends TestCase
{
    function testCreation()
    {
        $report = new ExtensionReport();
        $this->assertInstanceOf(ExtensionReport::class, $report);
    }

    /**
     *
     */
    function testAdding()
    {
        $report = new ExtensionReport();
        $node = new INode(__FILE__);
        $report->add($node);
        $reportArray['extension'] = $report->getExtensions();
        $this->assertEquals(1, $reportArray['extension']['.php']);
    }

    function testDump()
    {
        $report = new ExtensionReport();
        $report->add(new INode(__FILE__));
        $report->add(new INode(__DIR__));

        $handle = fopen('php://memory', 'r+');
        $report->dump($handle);
        rewind($handle);
        $buffer = stream_get_contents($handle);
        fclose($handle);
        $this->assertInternalType('string', $buffer);
        $this->assertGreaterThan(8, strlen($buffer));
    }
}
