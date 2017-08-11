<?php declare(strict_types=1);

/*
 * dateipolizei
 */

namespace Ktomk\DateiPolizei\String;


use InvalidArgumentException;

class PcreMatcher implements Matcher
{
    /**
     * @var array
     */
    private $patterns = [];

    /**
     * @var bool
     */
    private $default;

    /**
     * PcreMatcher constructor.
     *
     * @param bool $default [optional] matching with no patterns
     */
    public function __construct($default = true)
    {
        $this->default = $default;
    }

    public static function isValid(string $pattern)
    {
        return false !== @preg_match($pattern, "");
    }

    /**
     * Add pattern to matcher
     *
     * @param string $pattern
     * @param bool $invert the pattern, non-match becomes a match if true
     * @throws InvalidArgumentException
     */
    public function addPattern(string $pattern, bool $invert = false)
    {
        if (!self::isValid($pattern)) {
            throw new InvalidArgumentException(
                sprintf('Invalid PCRE pattern: "%s"', $pattern)
            );
        }
        $this->patterns[] = [$pattern, $invert];
    }

    /**
     * Match a string
     *
     * @param string $subject
     * @return bool true for a match, false otherwise
     */
    public function match(string $subject): bool
    {
        foreach ($this->patterns as [$pattern, $invert]) {
            $match = (1 - $invert) === preg_match($pattern, $subject);
            if ($match) {
                return true;
            }
        }

        return $this->patterns ? false : $this->default;
    }
}
