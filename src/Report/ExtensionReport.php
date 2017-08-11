<?php

declare(strict_types=1);

/*
 * dateipolizei
 *
 * Date: 03.08.17 08:34
 */

namespace Ktomk\DateiPolizei\Report;

use Ktomk\DateiPolizei\Fs\INode;

class ExtensionReport implements Report
{
    /**
     * @var ReportArray
     */
    private $report;

    /**
     * @var SegmentCountReservoir
     */
    private $reservoir;

    /**
     * ExtensionReport constructor.
     */
    public function __construct()
    {
        $this->report = new ReportArray([
            'ticks' => 0,
        ]);

        $this->reservoir = new SegmentCountReservoir(
            '.',
            SegmentCountReservoir::KEEP_EMPTY
        );
    }

    /**
     * Report an INode
     *
     * @param INode $node
     */
    public function add(INode $node)
    {
        $this->report->count('ticks');
        $this->reservoir->add($node->getBasename());
    }

    public function dump($handle = null)
    {
        $handle = $handle ?? STDOUT;

        $extensions = iterator_to_array($this->reservoir->getSegmentCounts(false));
        $extensions = $this->sortExtensions($extensions);

        $extLenMax = 0;
        $maxCount = 0;
        $countLenMax = 0;
        $entries = [];
        foreach ($extensions as $extension => $count) {
            $maxCount = max($maxCount, $count);
            $extension = strlen($extension) ? $extension : "-";
            $extLenMax = max($extLenMax, strlen($extension));
            $displayCount = number_format($count);
            $countLenMax = max($countLenMax, strlen($displayCount));
            $entries[] = [$extension, $displayCount, $count];
        }

        $mask = sprintf("%%' -%ds %%s %%s\n", $extLenMax);

        $barSize = max(39, 80 - $extLenMax - $countLenMax);

        foreach ($entries as [$extension, $displayCount, $count]) {
            $multiplier = 1 === $count ? 1 : 1 + (int)($barSize * $count / $maxCount);
            $bar = str_repeat('#', $multiplier);
            fprintf($handle, $mask, $extension, $bar, $displayCount);
        }
    }

    /**
     * @param bool $redundant [optional] include redundant strings, defaults to true
     * @return array
     */
    function getExtensions(bool $redundant = true): array
    {
        $generator = $this->reservoir->getSegmentCounts($redundant);

        return iterator_to_array($generator);
    }

    /**
     * @param $extensions
     * @return array
     */
    private function sortExtensions($extensions): array
    {
        $strings = array_keys($extensions);
        $counts = array_values($extensions);
        $result = array_multisort($counts, SORT_NUMERIC | SORT_DESC, $strings, SORT_STRING);
        if (false === $result) {
            // @codeCoverageIgnoreStart
            throw new \UnexpectedValueException('Internal: sorting extensions failed');
            // @codeCoverageIgnoreEnd
        }
        $extensions = array_combine($strings, $counts);
        return $extensions;
    }
}
