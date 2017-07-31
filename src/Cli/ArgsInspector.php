<?php declare(strict_types=1);

/*
 * dateipolizei
 *
 * Date: 23.07.17 00:04
 */

namespace Ktomk\DateiPolizei\Cli;

use Countable;

/**
 * Low-level command-line argument parser
 *
 * FIXME(tk): remove this, it should have passed it's live-span
 */
interface ArgsInspector extends Countable
{
    public function getCommand();

    public function hasSwitch(string $spec);

    public function parseShortSwitches(string $cmd, string ...$args): array;

    public function parseLongSwitches(string $cmd, string ...$args): array;

    /**
     * Count arguments
     *
     * Is at least one as the first argument is the name of command
     *
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     */
    public function count(): int;
}
