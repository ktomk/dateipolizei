<?php declare(strict_types=1);

/*
 * dateipolizei
 * 
 * Date: 22.07.17 23:38
 */

namespace Ktomk\DateiPolizei\Fs;

class RDirIter extends \RecursiveDirectoryIterator
{
    public function __construct($path, $flags)
    {
        if (is_readable($path)) {
        parent::__construct($path, $flags);
        }
        $this->setInfoClass(INode::class);
    }

    public function current(): Inode
    {
        $current = parent::current();
        return $current;
    }

    public function hasChildren($allow_links = null)
    {
        // do not traverse into non-readable directories
        if ($this->isDir() && !is_readable($this->getPath())) {
            return false;
        }

        return parent::hasChildren((bool)$allow_links); // TODO: Change the autogenerated stub
    }

}
