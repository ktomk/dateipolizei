<?php declare(strict_types=1);

/*
 * dateipolizei
 *
 * Date: 23.07.17 10:46
 */

namespace Ktomk\DateiPolizei\Cli;


/**
 * POSIX Utility Conventions style argument parser
 */
class ArgsTokenizer
{
    const S_DELIMITER = "--"; # end of options delimiter
    const S_DEF = "-"; #
    const S_EMPTY = "";

    /**
     * @var array|string[] input arguments
     */
    private $args = [];

    /**
     * @var array|string[]
     */
    private $tokens = [];

    public function addArguments(string ...$arguments)
    {
        foreach ($arguments as $argument) {
            $this->args[] = $argument;
        }
    }

    public function tokenize()
    {
        $this->tokens = [];
        $state = 0;
        $last = null;

        foreach ($this->args as $arg) {
            switch ($state) {
                case 0: # start
                    $this->tokens[] = $last = ArgsTokens::T_UTILITY;
                    $state++;
                    break;

                case 1: # options
                    if (self::S_DELIMITER === $arg) {
                        $this->tokens[] = $last = ArgsTokens::T_DELIMITER;
                        $state++;
                        break;
                    }
                    if (self::S_EMPTY === $arg || self::S_DEF === $arg || self::S_DEF !== $arg[0]) {
                        $this->tokens[] = $last = ArgsTokens::T_ARGUMENT;
                        break;
                    }
                    $this->tokens[] = $last = ArgsTokens::T_OPTION;
                    break;

                case 2: # operands
                    $this->tokens[] = $last = ArgsTokens::T_ARGUMENT;
                    break;

                default:
                    throw new \UnexpectedValueException(sprintf("Unhandled state '%s'", $state));
            }
        }
    }

    /**
     * @return array|string[]
     */
    public function getArguments(): array
    {
        return $this->args;
    }

    /**
     * @return array|string[]
     */
    public function getTokens(): array
    {
        return $this->tokens;
    }
}
