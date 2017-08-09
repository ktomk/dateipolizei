<?php

declare(strict_types=1);

/*
 * dateipolizei
 *
 * Date: 09.08.17 17:53
 */

namespace Ktomk\DateiPolizei\Fs;

/**
 * Class INodeIterFactory
 *
 * Global static factory method to create an INodeIter from a string
 * of a path.
 */
class INodeIterFactory
{
    /**
     * @param string $path
     * @return INodeIter
     * @throws \Exception
     */
    public function getIterator(string $path): INodeIter
    {
        if (is_dir($path)) {
            $dir = new RDirIter($path);
            $iter = new RRDirIter($dir, RRDirIter::SELF_FIRST);
        } elseif (is_file($path)) {
            $iter = new FileIter($path);
        }

        // FIXME(tk): what if path is neither a directory nor file
        if (!isset($iter)) {
            throw new \UnexpectedValueException(
                sprintf("Internal: Unknown type of path '%s'", $path)
            );
        }

        return $iter;
    }
}
