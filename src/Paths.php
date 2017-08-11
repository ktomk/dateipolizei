<?php declare(strict_types=1);

/*
 * dateipolizei
 *
 * Date: 02.08.17 22:18
 */

namespace Ktomk\DateiPolizei;

use Generator;
use UnexpectedValueException;

/**
 * Collection of (string) paths, as used for commandline option arguments or operators
 *
 * A path can be one to a directory or a file. The collection can contain both of them.
 */
class Paths implements \IteratorAggregate
{
    /**
     * @var array|string[]
     */
    private $paths;

    /**
     * working directory from CLI shell context
     *
     * The PWD environment variable is preferred as it represents the current working
     * directory in non- real-path form.
     *
     * Fallback if that (POSIX) environment variable is not set is PHPs own getcwd()
     * implementation.
     *
     * If in Fallback and that getcwd() function errors, an Exception is thrown.
     *
     * @return string
     */
    public static function getWorkingDirectory(): string
    {
        $cwd = getenv('PWD');

        if (false === $cwd) {
            $cwd = getcwd();
        }

        if (false === $cwd) {
            // @codeCoverageIgnoreStart
            throw new UnexpectedValueException(
                'Unable to obtain working directory, please file an issue with your system details'
            );
            // @codeCoverageIgnoreEnd
        }

        return $cwd;
    }

    /**
     * Paths constructor.
     *
     * Pass as many paths as you like, defaults to working directory
     *
     * @see getWorkingDirectory()
     *
     * @param string|null $firstPath [optional] defaults to working directory
     * @param string[] ...$paths zero or more additional paths
     */
    public function __construct(string $firstPath = null, string ...$paths)
    {
        if (null === $firstPath) {
            $firstPath = self::getWorkingDirectory();
        }

        $paths = array_values([-1 => $firstPath] + $paths);

        $this->paths = $paths;
    }

    public function set(string ...$paths)
    {
        $paths && $this->paths = $paths;
    }

    /**
     * Paths Collection has an unreadable (file or directory) path
     *
     * @return null|string [bool] null if there is no unreadable path, boolean true string with a message of the first one
     */
    public function hasUnreadable(): ?string
    {
        foreach ($this->paths as $path) {
            if (!is_dir($path) && !is_file($path)) {
                return sprintf("not a file or directory: '%s'", $path);
            }
            if (!is_readable($path)) {
                return sprintf("not readable: %s", $path);
            }
        }

        return null;
    }

    /* \IteratorAggregate implementation */

    /**
     * @return Generator|string[] paths of the collection
     */
    public function getIterator(): Generator
    {
        yield from $this->paths;
    }
}
