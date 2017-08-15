<?php declare(strict_types=1);

/*
 * dateipolizei
 *
 * Date: 22.07.17 23:17
 */

namespace Ktomk\DateiPolizei;

use Generator;
use IteratorAggregate;
use Ktomk\DateiPolizei\Fs\INodeIter;
use Ktomk\DateiPolizei\Fs\INodeIterFactory;
use Ktomk\DateiPolizei\String\Matcher;

/**
 * Class PathIter
 *
 * Default path-iteration implementation. Handles one or multiple paths
 */
class PathIter implements PathIterInterface
{
    /**
     * @var string[]
     */
    private $paths;

    /**
     * @var callable
     */
    private $visitor;
    /**
     * @var INodeIterFactory
     */
    private $factory;

    public static function create(string ...$path): self
    {
        $factory = new INodeIterFactory();

        return new self($factory, ...$path);
    }

    /**
     * PathIter constructor.
     * @param INodeIterFactory $factory
     * @param string[] ...$paths
     */
    private function __construct(INodeIterFactory $factory, string ...$paths)
    {
        $this->factory = $factory;
        $this->paths = $paths;
    }

    public function setIgnore(Matcher $ignore)
    {
        $this->factory->setIgnore($ignore);
    }

    /**
     * @return INodeIter
     */
    public function getIterator(): INodeIter
    {
        return new Fs\PathsIter($this->factory, ...$this->paths);
    }

    public function visit(callable $callable): void
    {
        $this->visitor = $callable;
    }

    /**
     * Iterate
     *
     * @return Generator with return count of items yielded
     */
    public function iterate(): Generator
    {
        $iter = $this->getIterator();
        $count = 0;

        foreach ($iter as $node) {
            if ($this->visitor) {
                $out = call_user_func($this->visitor, $node, $iter, $this);
            } else {
                $out = $iter->getSubPathName();
            }
            assert(is_null($out) || is_string($out));
            ++$count;
            yield $out;
        }

        return $count;
    }

    /**
     * @return Generator with return count of strings yielded
     */
    public function iterateStrings(): Generator
    {
        $iter = $this->iterate();
        $count = 0;
        foreach ($iter as $out) {
            if ($out !== null) {
                yield $out;
                ++$count;
            }
        }

        return $count;
    }

    /**
     * @return int count of output paths
     */
    public function dump(): int
    {
        $strings = $this->iterateStrings();
        foreach ($strings as $string) {
            echo $string, "\n";
        }

        $return = $strings->getReturn();
        assert(is_int($return));
        return (int) $strings->getReturn();
    }
}
