<?php

/*
 * dateipolizei
 *
 * Date: 09.08.17 17:41
 */

namespace Ktomk\DateiPolizei;

use IteratorAggregate;
use Ktomk\DateiPolizei\Fs\INodeIter;

interface PathIterInterface extends IteratorAggregate
{
    /**
     * @return INodeIter
     */
    public function getIterator(): INodeIter;

    /**
     * Add a callable that will visit each INode
     *
     * @param callable $callable
     */
    public function visit(callable $callable): void;

    /**
     * Echo out all lines of the iterator
     *
     * @return int count of output paths
     */
    public function dump(): int;
}
