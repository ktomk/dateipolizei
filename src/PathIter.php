<?php declare(strict_types=1);

/*
 * dateipolizei
 * 
 * Date: 22.07.17 23:17
 */

namespace Ktomk\DateiPolizei;

use Ktomk\DateiPolizei\Fs\FileIter;
use Ktomk\DateiPolizei\Fs\INodeIter;
use Ktomk\DateiPolizei\Fs\RDirIter;
use Ktomk\DateiPolizei\Fs\RRDirIter;

class PathIter
{
    /**
     * @var string
     */
    private $path;

    /**
     * @var callable
     */
    private $visitor;

    public static function create(string $path): self
    {
        return new self($path);
    }

    private function __construct(string $path)
    {
        $this->path = $path;
    }

    /**
     * @return INodeIter
     */
    private function getIterator(): INodeIter
    {
        if (is_dir($this->path)) {
            $dir = new RDirIter($this->path);
            $iter = new RRDirIter($dir, RRDirIter::SELF_FIRST);
        } elseif (is_file($this->path)) {
            $iter = new FileIter($this->path);
        }

        // FIXME(tk): what if path is neither a directory nor file
        if (!isset($iter)) {
            throw new \UnexpectedValueException(
                sprintf("Internal: Unknown type of path '%s'", $this->path)
            );
        }

        return $iter;
    }

    public function visit(callable $callable)
    {
        $this->visitor = $callable;
    }

    public function dump()
    {
        $iter = $this->getIterator();
        $count = 0;

        foreach ($iter as $inode) {
            if ($this->visitor) {
                $out = call_user_func($this->visitor, $inode, $iter, $this);
            } else {
                $out = $iter->getSubPathName();
            }
            if ($out !== null) {
                echo $out, "\n";
                ++$count;
            }
        }

        return $count;
    }
}
