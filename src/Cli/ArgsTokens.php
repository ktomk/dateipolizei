<?php declare(strict_types=1);

/*
 * dateipolizei
 *
 * Date: 23.07.17 12:53
 */

namespace Ktomk\DateiPolizei\Cli;

use OutOfBoundsException;
use SeekableIterator;

class ArgsTokens implements SeekableIterator
{
    const T_UTILITY = "(utility)"; # utility name
    const T_OPTION = "(option)"; # option (switch), starts with "-" or "--.."
    const T_ARGUMENT = "(argument)"; # option argument / operand
    const T_DELIMITER = "(delimiter)"; # "--" end of options delimiter

    /**
     * @var array s-expressions [[token, arg], ...]
     */
    private $tokens;

    /**
     * @var int iterator index
     */
    private $index = 0;

    public static function createFromArgs(...$args)
    {
        $tokenizer = new ArgsTokenizer();
        $tokenizer->addArguments(...$args);
        $tokenizer->tokenize();

        return new self($tokenizer->getTokens(), $tokenizer->getArguments());

    }

    /**
     * ArgsTokens constructor.
     *
     * @param array $tokens either tokens as s-expressions or tokens only
     * @param array|null $args [optional] arguments
     */
    public function __construct(array $tokens, array $args = null)
    {
        $this->tokens =
            (null === $args)
                ? $tokens
                : array_map(null, $tokens, $args);
    }

    /**
     * Consume the next arguments, until there is an option
     * or a delimiter
     *
     * @return array|null
     */
    public function consumeArguments(): ?array
    {
        $offset = $start = $this->index;
        $array = null;
        while (isset($this->tokens[$offset])) {
            [$token, $arg] = $this->tokens[$offset];
            if ($token !== self::T_ARGUMENT) {
                break;
            }
            $array[] = $arg;
            ++$offset;
        }

        $length = $offset - $start;
        if ($length) {
            array_splice($this->tokens, $start, $length);
        }

        --$this->index;

        return $array;
    }

    /**
     * Consume all remaining arguments and operands (incl. delimiter)
     *
     * @return array|string[] argument/operands (excl. delimiter)
     */
    public function consumeRemainingArguments(): ?array
    {
        $offset = $start = $this->index;

        $array = null;
        $valid = [self::T_ARGUMENT, self::T_DELIMITER];

        while (isset($this->tokens[$offset])) {
            [$token, $arg] = $this->tokens[$offset];
            if (!in_array($token , $valid, true)) {
                $this->tokenMismatch($valid, $token, $offset);
            }
            if ($token !== self::T_DELIMITER) {
                $array[] = $arg;
            }
            ++$offset;
        }

        if (0 === $start) {
            $this->tokens = [];
        } else {
            $this->tokens = array_slice($this->tokens, 0, $start);
        }

        --$this->index;

        return $array;
    }

    /**
     * @param string|null $token
     * @param bool|null $throws [optional] whether or not to throw exceptions if token is not available
     * @return string|null the consumed token argument or null if it was not available
     */
    public function consume(string $token = null, bool $throws = null): ?string
    {
        $offset = $this->index;

        $current = $this->tokens[$offset] ?? null;
        if (null === $current) {
            /** @noinspection PhpStrictTypeCheckingInspection */
            return $throws ? $this->noToken($offset) : null;
        }

        [$t, $a] = $current;
        if (null !== $token && $t !== $token) {
            /** @noinspection PhpStrictTypeCheckingInspection */
            return $throws ? $this->tokenMismatch($token, $t, $offset) : null;
        }

        if ($offset === 0) {
            array_shift($this->tokens);
        } else {
            unset($this->tokens[$offset]);
            $this->tokens = array_values($this->tokens);
        }

        $this->index = $offset - 1;

        return $a;
    }

    /**
     * Consume and next
     *
     * @see consume
     * @see next
     *
     * @param string|null $token
     * @param bool $throws [optional] defaults to throw exception
     * @return null|string
     */
    public function consumeNext(string $token = null, bool $throws = true): ?string
    {
        $result = $this->consume($token, $throws);
        if ($result !== null) {
            $this->next();
        }

        return $result;
    }

    private function noToken($offset)
    {
        Throw new OutOfBoundsException(sprintf("No token to consume at offset '%s'", $offset));
    }

    private function tokenMismatch($requested, $got, $offset)
    {
        if (is_array($requested)) {
            $requested = implode("'|'", $requested);
        }
        throw new \BadMethodCallException(
            sprintf("Token mismatch, requested '%s', got '%s' at offset '%s'", $requested, $got, $offset)
        );
    }

    /*
     * Iterator implementation
     */

    public function seek($position)
    {
        if (!isset($this->tokens[$position])) {
            throw new OutOfBoundsException("Invalid seek position '$position'");
        }

        $this->index = $position;
    }

    public function current(): ?array
    {
        return $this->tokens[$this->index] ?? null;
    }

    public function next()
    {
        $this->index++;
    }

    public function key(): int
    {
        return $this->index;
    }

    public function valid(): bool
    {
        return isset($this->tokens[$this->index]);
    }

    public function rewind()
    {
        $this->index = 0;
    }
}
