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
    public function __construct(\RecursiveIterator $iterator, int $mode = self::LEAVES_ONLY, int $flags = 0)
    {
        assert(
            method_exists($iterator, 'getSubPathname'),
            'light check  that iterator is RDirIter or extended'
        );
        assert(
            0 === strpos(get_class($iterator), __NAMESPACE__ . '\\'),
            'check that iterator is from within namespace'
        );
        parent::__construct($iterator, $mode, $flags);
    }

    public function getSubPathname(): string
    {
        return $this->getInnerIterator()->getSubPathname();
    }

    public function current(): INode
    {
        return $this->getInnerIterator()->current();
    }
}
