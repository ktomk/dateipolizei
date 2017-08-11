<?php declare(strict_types=1);

/*
 * dateipolizei
 *
 * Date: 04.08.17 23:34
 */

namespace Ktomk\DateiPolizei\String;


class InPathMatcher implements Matcher
{
    /**
     * @var PcreMatcher
     */
    private $pcre;

    /**
     * @var array segments as keys
     */
    private $segments;

    public function __construct()
    {
        $this->segments = [];
        $this->pcre = new PcreMatcher(false);
    }

    public function addSegment(string $segment): void
    {
        if (isset($this->segments[$segment])) {
            return;
        }

        $this->segments[$segment] = true;
        $pattern = sprintf('~(?:^|/)%s(?:/|$)~', preg_quote($segment));
        $this->pcre->addPattern($pattern);
    }

    public function addSegments(string ...$segments): void
    {
        foreach ($segments as $segment) {
            $this->addSegment($segment);
        }
    }

    /**
     * @return array|string[] of segments, sorted
     */
    public function getSegments(): array
    {
        $segments = array_map('strval', array_keys($this->segments));

        if (false === sort($segments)) {
            // @codeCoverageIgnoreStart
            throw new \UnexpectedValueException('Internal: Sorting failed');
            // @codeCoverageIgnoreEnd
        }

        return $segments;
    }

    /**
     * Match a string
     *
     * @param string $subject
     * @return bool true for a match, false otherwise
     */
    public function match(string $subject): bool
    {
        return $this->pcre->match($subject);
    }
}
