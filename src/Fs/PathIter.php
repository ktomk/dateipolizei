<?php declare(strict_types=1);

/*
 * dateipolizei
 *
 * Date: 09.08.17 19:09
 */

namespace Ktomk\DateiPolizei\Fs;

use IteratorIterator;

/**
 * Class PathIter
 *
 * INodeIter to iterate over a single path.
 *
 * @method INodeIter getInnerIterator()
 */
class PathIter extends IteratorIterator implements INodeIter
{
    /**
     * PathIter constructor.
     *
     * @param INodeIterFactory $factory
     * @param string $path
     */
    public function __construct(INodeIterFactory $factory, string $path)
    {
        $iter = $factory->getIterator($path);
        parent::__construct($iter);
    }

    /**
     * @inheritDoc
     */
    public function current(): INode
    {
        return $this->getInnerIterator()->current();
    }

    /**
     * @inheritDoc
     */
    public function getSubPathname(): string
    {
        return $this->getInnerIterator()->getSubPathname();
    }
}
