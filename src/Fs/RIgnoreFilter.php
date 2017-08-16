<?php

declare(strict_types=1);

/*
 * dateipolizei
 *
 * Date: 13.08.17 22:22
 */

namespace Ktomk\DateiPolizei\Fs;

use Ktomk\DateiPolizei\String\Matcher;
use Ktomk\DateiPolizei\String\Matcher\PatternMatcher;
use RecursiveFilterIterator;
use RecursiveIterator;

/**
 * Class RIgnoreFilter
 *
 * Ignore (prune) directories by name
 *
 * @method \RecursiveIterator getInnerIterator()
 */
class RIgnoreFilter extends RecursiveFilterIterator
{
    /**
     * @var Matcher|\Ktomk\DateiPolizei\String\Matcher\PatternMatcher
     */
    private $matcher;

    /**
     * @inheritDoc
     */
    public function __construct(RecursiveIterator $iterator, Matcher $matcher)
    {
        parent::__construct($iterator);

        $this->matcher = $matcher;
    }


    public function accept()
    {
        $current = $this->getInnerIterator()->current();
        assert($current instanceof INode);

        if ($current->isDir()) {
            return !$this->matcher->match($current->getFilename());
        }

        return true;
    }

    public function getChildren()
    {
        return new self($this->getInnerIterator()->getChildren(), $this->matcher);
    }
}
