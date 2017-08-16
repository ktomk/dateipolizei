<?php

declare(strict_types=1);

/*
 * dateipolizei
 *
 * Date: 13.08.17 22:57
 */

namespace Ktomk\DateiPolizei\String\Matcher;


use Ktomk\DateiPolizei\String\Matcher;

class PatternMatcher implements Matcher
{
    /**
     * limit of patterns per each string pattern
     *
     * a string can contain multiple patterns
     */
    const LIMIT = 512;

    /**
     * @var array|string[]
     */
    private $patterns = [];

    /**
     * @var string PCRE pattern for all $patterns
     */
    private $pcre;

    /**
     * @param string $pattern
     */
    public function addPattern(string $pattern): void
    {
        $pattern = preg_split('~\\\\.(*SKIP)(*FAIL)|;~s', $pattern, self::LIMIT);

        foreach ($pattern as $single) {
            if (!strlen($single)) {
                $this->patterns[$single] = $single;
                continue;
            }

            // TODO(tk): exclusion pattern as such (negative match) needed?
            if ('!' === $single[0]) {
                $single = strtr(substr($single, 1), ['\!' => '!']);
                unset($this->patterns[$single]);
                continue;
            }

            if (strlen($single) > 1 && '\!' === $single[0] . $single[1]) {
                $single = substr($single, 1);
            }

            $this->patterns[$single] = $single;
        }

        $this->pcre = $this->compilePcre();
    }

    /**
     * @param iterable $patterns string patterns
     */
    public function addPatterns(iterable $patterns)
    {
        foreach ($patterns as $pattern) {
            $this->addPattern($pattern);
        }
    }

    /**
     * @return array|string[]
     */
    public function getPatterns(): array
    {
        return array_keys($this->patterns);
    }

    public function clearPatterns()
    {
        $this->patterns = [];
        $this->pcre = $this->compilePcre();
    }

    private function compilePcre(): string
    {
        $subPatterns = array_map([$this, 'pcrePattern'], $this->patterns);

        $pcre = sprintf('~^(?:%s)$~', implode('|', $subPatterns));

        $result = @preg_match($pcre, "");
        assert(false !== $result, 'Compiling PCRE pattern failed');

        return $pcre;
    }

    private function pcrePattern(string $pattern): string
    {
        $pcre = strtr(
            $pattern,
            [
                '\\\\' => '[\\\\]', '\\;' => '[;]', '\\' => '[\\\\]',
                '^' => '\\^', '$' => '[$]',
                '|' => '[|]', '+' => '[+]',
                '[' => '[[]', ']' => '[]]',
                '(' => '[(]', ')' => '[)]',
                '{' => '[(]', '}' => '[)]',
                '<' => '[<]', '>' => '[>]',
                '*' => '[^/]*', '?' => '[^/]?', '.' => '[.]',
                '=' => '[=]', '!' => '[!]',
                ':' => '[:]', '-' => '[-]',
                '~' => '\\~',
            ]
        );

        return $pcre;
    }

    /* Matcher implementation */

    /**
     * @inheritdoc
     */
    public function match(string $subject): bool
    {
        if (!$this->patterns) {
            return false;
        }

        return (bool)preg_match($this->pcre, $subject);
    }
}
