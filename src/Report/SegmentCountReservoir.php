<?php declare(strict_types=1);

/*
 * dateipolizei
 *
 * Date: 10.08.17 08:32
 */

namespace Ktomk\DateiPolizei\Report;

use Generator;
use InvalidArgumentException;

/**
 * Class SegmentReservoir
 *
 * Add prefix-segmented strings to reservoir for counting.
 *
 * Obtain either full segment counts -or- with redundant sub-segments removed.
 * For redundancy largest wins, redundancy lookup is the driver of this reservoir
 *
 * Example:
 *
 * < prefix: .
 * < string: phpunit.xml.dist
 * < string: phpcs.xml.dist
 * > segment: .xml.dist #2
 * > segment: .dist     #2 (redundant)
 */
class SegmentCountReservoir
{

    /**
     * @var array
     */
    private $tree = [];

    /**
     * @var array
     */
    private $map = [];

    const NONE = 0;
    const FLAGS_DEFAULT = self::NONE;


    /**
     * When splitting a string that is of zero length, whether to keep this zero-length
     * string as a single part or not.
     */
    const KEEP_EMPTY = 1;

    // FIXME(tk): drop KEEP_EMPTY as default as it's not the default actually, check naming, too

    /**
     * @var string
     */
    private $prefix;

    /**
     * @var int
     */
    private $flags;

    /**
     * SegmentCountReservoir constructor.
     * @param string $prefix non-zero-length string to use as prefix
     * @param int $flags [optional]
     */
    public function __construct(string $prefix, ?int $flags = self::FLAGS_DEFAULT)
    {
        if (!strlen($prefix)) {
            throw new InvalidArgumentException("Zero-length prefix given");
        }

        if (null === $flags) {
            $flags = self::KEEP_EMPTY;
        }

        $this->prefix = $prefix;
        $this->flags = $flags;
    }

    /**
     * Create an array of segments
     *
     * Utility function that needs extended explanation:
     *
     * Start first segment on $prefix, $prefix is included in each segment. From left
     * to right, the first segment is the largest. This is no ordinary split:
     *
     *    /path/to/file  -- / -->  /path/to/file
     *                             /to/file
     *                             /file
     *
     * TODO(tk): drop first empty segment / accept filled first non-prefixed segment
     *
     * @param string $string
     * @param string $prefix
     * @param int $flags
     * @return array
     */
    public function makeOnPrefix(string $string, string $prefix, ?int $flags = self::FLAGS_DEFAULT)
    {
        $len = strlen($prefix);
        if (!$len) {
            throw new InvalidArgumentException("Zero-length prefix given");
        }

        if (null === $flags) {
            $flags = self::FLAGS_DEFAULT;
        }

        $segments = [];
        $offset = 0;

        while (false !== $pos = strpos($string, $prefix, $offset)) {
            $segments[] = substr($string, $pos);
            $offset = $pos + $len;
        }

        $segments
        || ($flags & self::KEEP_EMPTY && (false !== $pos || '' === $string))
        && $segments[] = "";

        return $segments;
    }

    /**
     * Add string to reservoir
     *
     * @param string $string
     * @param int $count [default] numbers to count
     * @internal param string $prefix
     * @internal param int $flags [optional]
     */
    public function add(string $string, int $count = 1)
    {
        $prefix = $this->prefix;
        $flags = $this->flags;

        $parts = $this->makeOnPrefix($string, $prefix, $flags);

        $ref = &$this->tree;
        while (null !== $part = array_pop($parts)) {
            $segment = strlen($string) ? $this->first($part, $prefix) : '';
            if (!isset($ref[$segment])) {
                $ref[$segment] = [[], 0];
            }
            $vRef = &$ref[$segment];
            $vRef[1] += $count;
            $this->map[$part] = &$vRef;
            $ref = &$vRef[0];
        }
    }

    /**
     * Fill reservoir with zero or more strings
     *
     * @param iterable $strings
     * @internal param string $prefix
     * @internal param int $flags [optional]
     */
    public function fill(iterable $strings)
    {
        foreach ($strings as $string) {
            $this->add($string);
        }
    }

    /**
     * Fill reservoir with zero or more string => count (s)
     *
     * @param iterable $countedStrings string => (int) count
     */
    public function fillCounts(iterable $countedStrings)
    {
        foreach ($countedStrings as $string => $count) {
            $this->add($string, $count);
        }
    }

    /**
     * @param bool $redundant [optional] include redundant strings, defaults to true
     * @return Generator
     */
    public function getSegmentCounts(bool $redundant = true): Generator
    {
        foreach ($this->map as $segment => [, $count]) {

            // redundant filter
            if (
                !$redundant
                && ($parent = $this->map[$segment][0])
                && 1 === count($parent)
                && reset($parent)[1] === $count
            ) {
                continue;
            }

            yield $segment => $count;
        }
    }

    /**
     * get first segment of string
     *
     * this method is in old mode of not accepting non-prefixed first segments, so this
     * is the (true(tm)) first segment starting (and including) prefix.
     *
     * @param string $string having at least one prefixed segment at offset 0
     * @param string $prefix
     * @return string first segment
     */
    private function first(string $string, string $prefix): string
    {
        $cut = strpos($string, $prefix, strlen($prefix));
        if ($cut === false) {
            return $string;
        }
        return substr($string, 0, $cut);
    }
}
