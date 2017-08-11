<?php declare(strict_types=1);

/*
 * dateipolizei
 *
 * Date: 24.07.17 22:09
 */

namespace Ktomk\DateiPolizei\Report;

use Ktomk\DateiPolizei\Fs\INode;
use Ktomk\DateiPolizei\Printer;

/**
 * Class INodeReport
 *
 * Aggregate information over INodes
 */
class INodeReport implements Report
{
    /**
     * @var ReportArray
     */
    private $report;

    public function __construct()
    {
        $this->report = new ReportArray([
            # counters
            'ticks' => 0,
            'file' => 0,
            'dir' => 0,
            'link' => 0,

            # counters by parts
            'name' => [],
            'extension' => [],
            'dirname' => [],
            'filename' => [],
        ]);
    }

    /**
     * Report an INode
     *
     * @param INode $INode
     */
    public function add(INode $INode)
    {
        $report = $this->report;

        $report->count('ticks');

        $name = $INode->getFilename();
        $report->countName('name', $name);

        $report->countName('extension', $INode->getExtension());

        if ($INode->isDir()) {
            $report->count('dir');
            $report->countName('dirname', $name);
        };

        if ($INode->isFile()) {
            $report->count('file');
            $report->countName('filename', $name);
        }

        if ($INode->isLink()) {
            $report->count('link');
        }
    }

    public function dump($handle = null)
    {
        $printer = new Printer($handle);
        $report = $this->report;

        $filesPerDir = $report['dir'] ? $report['file'] / $report['dir'] : $report['file'];
        $dirDistribution = $report['ticks'] ? $report['dir'] / $report['ticks'] : 0;
        $extensions = count($report['extension']);

        $printer->numberList([
            "path(s)" => number_format($report['ticks']),
            "directory(/ ies)" => number_format($report['dir']),
            "file(s)" => number_format($report['file']),
            "extension(s)" => number_format($extensions),
            "dir distribution" => sprintf("%.1f %%", $dirDistribution * 100),
            "files per dir" => sprintf("%.3f", $filesPerDir),
            "link(s)" => number_format($report['link']),
        ]);
    }

    /**
     * @return ReportArray
     */
    public function getReport(): ReportArray
    {
        return $this->report;
    }
}
