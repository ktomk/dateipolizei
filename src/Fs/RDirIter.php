<?php declare(strict_types=1);

/*
 * dateipolizei
 *
 * Date: 22.07.17 23:38
 */

namespace Ktomk\DateiPolizei\Fs;

/**
 * Class RDirIter
 *
 * Recursive directory iterator which handles the case of unreadable
 * directories. These don't throw an exception any longer but can be
 * part of a (parent iterator) listing
 *
 */
class RDirIter extends \RecursiveDirectoryIterator
{
    public function __construct($path, $flags = null)
    {
        if (null === $flags) {
            $flags = RDirIter::SKIP_DOTS | RDirIter::UNIX_PATHS;
        }

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
        if ($this->isDir() && !is_readable($this->getPathname())) {
            return false;
        }

        // TODO(tk): Change the auto-generated stub in Phpstorm
        return parent::hasChildren((bool)$allow_links);
    }

}
