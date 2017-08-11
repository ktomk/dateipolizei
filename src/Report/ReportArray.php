<?php

declare(strict_types=1);

/*
 * dateipolizei
 *
 * Date: 03.08.17 08:37
 */

namespace Ktomk\DateiPolizei\Report;

use ArrayAccess;
use JsonSerializable;

class ReportArray implements ArrayAccess, JsonSerializable
{
    /**
     * @var array
     */
    private $report;

    /**
     * @var array
     */
    private $namedCounters;

    /**
     * @var array
     */
    private $counters;

    /**
     * ReportArray constructor.
     *
     * @param array $report
     */
    public function __construct(array $report)
    {
        if ([] === $report) {
            throw new \BadMethodCallException('Report must not be empty');
        }

        $this->report = $report;

        $this->namedCounters = array_filter($this->report, 'is_array');
        $this->counters = array_diff_key($this->report, $this->namedCounters);
    }

    public function count(string $counter)
    {
        $this->validateCounterArgument($counter, $this->counters);
        $thatCounter = &$this->report[$counter];
        $thatCounter = ($thatCounter ?? 0) + 1;
    }

    /**
     * @param string $counter
     * @param string $object
     */
    public function countName(string $counter, string $object)
    {
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

    /* @see \ArrayAccess implementation */

    /**
     * @param mixed $offset
     * @return bool|void
     */
    public function offsetExists($offset)
    {
        throw new \BadMethodCallException(
            sprintf("Can never check existence, not even for '%s'", $offset)
        );
    }

    /**
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        $this->validateCounterArgument($offset, $this->report);
        return $this->report[$offset];
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        throw new \BadMethodCallException(
            sprintf("Can never set, not even '%s'", $offset)
        );
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        throw new \BadMethodCallException(
            sprintf("Can never unset, not even '%s'", $offset)
        );
    }

    /* @see \JsonSerializable implementation */

    /**
     * @return array
     */
    function jsonSerialize(): array
    {
        return $this->report;
    }
}
