<?php

declare(strict_types=1);

/*
 * dateipolizei
 *
 * Date: 03.08.17 22:38
 */

namespace Ktomk\DateiPolizei\Report;

use Ktomk\DateiPolizei\Fs\INode;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Ktomk\DateiPolizei\Report\INodeReport
 */
class INodeReportTest extends TestCase
{
    function testCreation()
    {
        $report = new INodeReport();
        $this->assertInstanceOf(INodeReport::class, $report);
    }

    function testAdding()
    {
        $report = new INodeReport();
        $node = new INode(__FILE__);
        $report->add($node);
        $this->assertEquals(0, $report->getReport()['dir']);
        $node = new INode(__DIR__);
        $report->add($node);
        $this->assertEquals(1, $report->getReport()['dir']);

        $node = new INode($this->createTempLink());
        $report->add($node);
        $this->assertEquals(1, $report->getReport()['link']);
        unlink((string)$node);
    }

    private function createTempLink()
    {
        $link = tempnam(sys_get_temp_dir(), 'sl');
        unlink($link);
        symlink(__FILE__, $link);

        return $link;
    }

    function testDump()
    {
        $report = new INodeReport();
        $handle = fopen('php://memory', 'r+');
        $report->dump($handle);
        rewind($handle);
        $buffer = stream_get_contents($handle);
        fclose($handle);
        $this->assertInternalType('string', $buffer);
        $this->assertGreaterThan(64, strlen($buffer));
    }
}
