<?php

/*
 * dateipolizei
 *
 * Date: 25.07.17 21:10
 */

namespace Ktomk\DateiPolizei\Fs;

use Iterator;

/**
 * Interface INodeIter
 *
 * @package Ktomk\DateiPolizei\Fs
 */
interface INodeIter extends Iterator
{
    /**
     * Path w/o basedir
     *
     * @return string
     */
    public function getSubPathname(): string;

    public function current(): INode;
}
