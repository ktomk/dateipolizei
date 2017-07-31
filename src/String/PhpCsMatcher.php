<?php declare(strict_types=1);

/*
 * dateipolizei
 *
 * Date: 26.07.17 18:56
 */

namespace Ktomk\DateiPolizei\String;


/**
 * String matcher for paths based on a set of PHPCS exclude and include pattern
 */
class PhpCsMatcher implements Matcher
{
    /**
     * Rules to match against
     *
     * @var array
     */
    private $rules = [
        'exclude' => [],
        'include' => [],
    ];

    public static function isValid(string $pattern)
    {
        $pcre = self::patternPcre($pattern);

        return PcreMatcher::isValid($pcre);
    }

    /**
     * Convert PHP_CodeSniffer exclude pattern into PCRE regex
     *
     * This mimics the string manipulation as of phpcs 2.9
     * @see \PHP_CodeSniffer::shouldIgnoreFile
     *
     * @param string $pattern
     * @param string $directorySeparator to use, allows to inject a different file-system
     * @return string PCRE pattern
     */
    public static function patternPcre(string $pattern, string $directorySeparator = DIRECTORY_SEPARATOR): string
    {
        $replacements = array(
            '\\,' => ',',
            '*' => '.*',
        );

        // We assume a / directory separator, as do the exclude rules
        // most developers write, so we need a special case for any system
        // that is different.
        if ($directorySeparator === '\\') {
            $replacements['/'] = '\\\\';
        }
        $buffer = strtr($pattern, $replacements);
        $pcrePattern = sprintf('`%s`i', $buffer);

        return $pcrePattern;
    }

    public function addIncludeRule($phpcsPattern)
    {
        $this->rules['include'][] = self::patternPcre($phpcsPattern);
    }

    public function addExcludeRule($phpcsPattern)
    {
        $this->rules['exclude'][] = self::patternPcre($phpcsPattern);
    }

    public function match(string $subject): bool
    {
        // include rules override all exclude rules, PHP_CodeSniffer 3 behaviour
        foreach ($this->rules['include'] as $pattern) {
            if (preg_match($pattern, $subject)) {
                return true;
            }
        }

        if ($this->rules['include']) {
            return false;
        }

        foreach ($this->rules['exclude'] as $pattern) {
            if (preg_match($pattern, $subject)) {
                return false;
            }
        }

        return true;
    }
}
