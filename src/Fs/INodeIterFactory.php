<?php

declare(strict_types=1);

/*
 * dateipolizei
 *
 * Date: 09.08.17 17:53
 */

namespace Ktomk\DateiPolizei\Fs;

use Ktomk\DateiPolizei\String\Matcher;

/**
 * Class INodeIterFactory
 *
 * Global static factory method to create an INodeIter from a string
 * of a path.
 */
class INodeIterFactory
{
    /**
     * @var Matcher|null
     */
    private $ignore;

    /**
     * INodeIterFactory constructor.
     * @param Matcher|null $ignore [optional]
     */
    public function __construct(Matcher $ignore = null)
    {
        $this->ignore = $ignore;
    }

    /**
     * @param string $path
     * @return INodeIter
     * @throws \Exception
     */
    public function getIterator(string $path): INodeIter
    {
        if (is_dir($path)) {
            $dir = new RDirIter($path);
            if ($this->ignore) {
                $dir = new RIgnoreFilter($dir, $this->ignore);
            }
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

    /**
     * @param Matcher|null $ignore
     */
    public function setIgnore(?Matcher $ignore)
    {
        $this->ignore = $ignore;
    }
}
