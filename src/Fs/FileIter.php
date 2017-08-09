<?php declare(strict_types=1);

/*
 * dateipolizei
 *
 * Date: 25.07.17 21:06
 */

namespace Ktomk\DateiPolizei\Fs;

use Iterator;

/**
 * Class FileIter
 *
 * INodeIter of a single file-path. Useful to process input files within
 * INode iterations.
 */
class FileIter implements Iterator, INodeIter
{
    /**
     * @var string
     */
    private $path;

    /**
     * @var int
     */
    private $index = 0;

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

    public function rewind()
    {
        $this->index = 0;
    }

    public function valid(): bool
    {
        return 0 === $this->index;
    }

    public function key()
    {
        return $this->index ? null : 0;
    }

    public function current(): INode
    {
        return new INode($this->path);
    }

    public function next()
    {
        $this->index || ++$this->index;
    }
}
