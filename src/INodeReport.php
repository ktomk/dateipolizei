<?php declare(strict_types=1);

/*
 * dateipolizei
 * 
 * Date: 24.07.17 22:09
 */

namespace Ktomk\DateiPolizei;

use Ktomk\DateiPolizei\Fs\INode;

/**
 * Class INodeReport
 *
 * Aggregate information over INodes
 */
class INodeReport
{
    /**
     * @var array
     */
    private $report;

    /**
     * @var array
     */
    private $namedCounters;
    private $counters;

    public function __construct()
    {
        $this->report = [
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
        ];

        $this->namedCounters = array_filter($this->report, 'is_array');
        $this->counters = array_diff_key($this->report, $this->namedCounters);
    }

    /**
     * Report an INode
     *
     * @param INode $INode
     */
    public function add(INode $INode)
    {
        $this->count('ticks');

        $name = $INode->getFilename();
        $this->countName('name', $name);

        $this->countName('extension', $INode->getExtension());

        $isDir = $INode->isDir();
        $this->count('dir', $isDir);
        $this->countName('dirname', $name, $isDir);

        $isFile = $INode->isFile();
        $this->count('file', $isFile);
        $this->countName('filename', $name, $isFile);

        $isLink = $INode->isLink();
        $this->count('link', $isLink);
    }

    private function count(string $counter, $if = true)
    {
        if (!$if) {
            return;
        }
        $this->validateCounterArgument($counter, $this->counters);
        $thatCounter = &$this->report[$counter];
        $thatCounter = ($thatCounter ?? 0) + 1;
    }

    /**
     * @param string $counter
     * @param string $object
     * @param bool $if [optional]
     */
    private function countName(string $counter, string $object, $if = true)
    {
        if (!$if) {
            return;
        }
        $this->validateCounterArgument($counter, $this->namedCounters);
        $thatCounter = &$this->report[$counter][$object];
        $thatCounter = ($thatCounter ?? 0) + 1;
    }

    /**
     * @param string $counter
     * @param $counters
     */
    private function validateCounterArgument(string $counter, $counters): void
    {
        if (!isset($counters[$counter])) {
            throw new \InvalidArgumentException(
                sprintf(
                    "Invalid counter '%s', must be '%s'",
                    $counter,
                    implode(
                        "' or '",
                        array_keys($counters)
                    )
                )
            );
        }
    }

    public function dump($handle = null)
    {
        $printer = new Printer($handle);
        $report = $this->report;

        $filesPerDir = $report['dir'] ? $report['file'] / $report['dir'] : $report['file'];
        $dirDistribution = $report['ticks'] ? $report['dir'] / $report['ticks'] : 0;

        $printer->numberList([
            "path(s)" => number_format($report['ticks']),
            "directory(/ ies)" => number_format($report['dir']),
            "file(s)" => number_format($report['file']),
            "dir distribution" => sprintf("%.1f %%", $dirDistribution * 100),
            "files per dir" => sprintf("%.3f", $filesPerDir),
            "link(s)" => number_format($report['link']),
        ]);
    }
}
