<?php declare(strict_types=1);

/*
 * dateipolizei
 * 
 * Date: 23.07.17 12:48
 */

namespace Ktomk\DateiPolizei;

use Ktomk\DateiPolizei\Cli\ArgsTokens;

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
}
