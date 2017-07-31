<?php declare(strict_types=1);

/*
 * dateipolizei
 * 
 * Date: 22.07.17 23:46
 */

namespace Ktomk\DateiPolizei\Fs;


use RecursiveIteratorIterator;

/**
 * Class RRDirIter
 *
 * @method INodeIter getInnerIterator()
 */
class RRDirIter extends RecursiveIteratorIterator implements INodeIter
{
    public function __construct(RDirIter $iterator, int $mode = self::LEAVES_ONLY, int $flags = 0)
    {
        parent::__construct($iterator, $mode, $flags);
    }

    public function getSubPathname(): string
    {
        return $this->getInnerIterator()->getSubPathname();
    }

    public function current(): INode
    {
        return parent::current();
    }
}
