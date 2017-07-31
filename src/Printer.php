<?php declare(strict_types=1);

/*
 * dateipolizei
 * 
 * Date: 25.07.17 08:30
 */

namespace Ktomk\DateiPolizei;


class Printer
{
    private $handle;

    public function __construct($handle = null)
    {
        if (null === $handle) {
            $handle = STDOUT;
        }

        $this->handle = $handle;
    }

    public function topList(array $list)
    {
        $topLinks = $list;
        asort($topLinks, SORT_DESC);

        foreach ($this->limit($topLinks, 7) as $name => $count) {
            fprintf($this->handle, "  %s (%s)\n", $name, number_format($count));
        }
    }

    public function limit(iterable $iter, $max)
    {
        $max = max(0, $max);
        $index = 0;
        foreach ($iter as $key => $value) {
            if ($index > $max) {
                return $index;
            }
            yield $key => $value;
            ++$index;
        }
        return 0;
    }

    /**
     * @param array $numbers
     */
    public function numberList(array $numbers)
    {
        $max = [0, 0];
        foreach ($numbers as $label => $number) {
            $max[0] = max($max[0], strlen($label));
            $max[1] = max($max[1], strlen($number));
        }

        $format = vsprintf("%%'.-%ds: %%' %ds\n", $max);

        foreach ($numbers as $label => $number) {
            fprintf($this->handle, $format, $label, $number);
        }
    }

    /**
     * @return resource
     */
    public function getHandle()
    {
        return $this->handle;
    }
}
