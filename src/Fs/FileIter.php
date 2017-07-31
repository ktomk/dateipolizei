<?php declare(strict_types=1);

/*
 * dateipolizei
 * 
 * Date: 25.07.17 21:06
 */

namespace Ktomk\DateiPolizei\Fs;


class FileIter implements \IteratorAggregate, INodeIter
{
    /**
     * @var string
     */
    private $path;

    /**
     * FileIter constructor.
     *
     * @param string $path
     */
    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function getSubPathname(): string
    {
        return $this->path;
    }

    public function current(): INode
    {
        return new INode($this->path);
    }

    public function getIterator()
    {
        yield $this->current();
    }
}
