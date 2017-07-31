<?php

/*
 * dateipolizei
 * 
 * Date: 25.07.17 21:10
 */

namespace Ktomk\DateiPolizei\Fs;

use Traversable;

/**
 * Interface INodeIter
 *
 * @package Ktomk\DateiPolizei\Fs
 */
interface INodeIter extends Traversable
{
    public function getSubPathname(): string;
    public function current(): INode;
}
