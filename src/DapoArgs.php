<?php declare(strict_types=1);

/*
 * dateipolizei
 *
 * Date: 23.07.17 12:48
 */

namespace Ktomk\DateiPolizei;

use Ktomk\DateiPolizei\Cli\ArgsTokens;
use Ktomk\DateiPolizei\String\Matcher\PatternMatcher;

/**
 * Global dateipolizei arguments object for commandline arguments parsing
 * in (CLI) application context.
 *
 * Public properties store global values that dapo commands can use (or are
 * used with).
 *
 * TODO(tk): verbosity setting(s) when needed
 */
class DapoArgs
{
    /* public properties as of dateipolizei arguments */

    public $utility;
    public $utility_name;

    public $exec_path;

    public $print_usage_info = true;

    /* private properties */

    private $command;

    /**
     * @var ArgsTokens
     */
    private $tokens;

    /**
     * @var DapoConfig
     */
    private $config;

    /**
     * @var PatternMatcher Ignore pattern matcher used in the process
     */
    private $ignore;

    public static function create(string $bin, string ...$args): self
    {
        return new self($bin, ...$args);
    }

    private function __construct(string $bin, string ...$args)
    {
        $this->exec_path = dirname(dirname($bin)) . '/cli';

        $tokens = ArgsTokens::createFromArgs(...$args);

        $this->utility = $tokens->consume(ArgsTokens::T_UTILITY);
        $this->utility_name = basename($this->utility);

        $this->tokens = $tokens;

        $this->config = new DapoConfig();
    }

    public function getCommand(): string
    {
        if ($this->command === null) {
            $this->command = (string) $this->tokens->consume(ArgsTokens::T_ARGUMENT);
        }

        return $this->command;
    }

    public function getTokens(): ArgsTokens
    {
        return $this->tokens;
    }

    /**
     * TODO(tk): global ignore manipulating commands should chime in when this progresses on well
     *           that is: instead of PatternMatcher some more specialized ignore object
     *
     * @return \Ktomk\DateiPolizei\String\Matcher\PatternMatcher
     */
    public function getIgnore(): PatternMatcher
    {
        return $this->ignore ?? $this->ignore = $this->config->getIgnore();
    }
}
