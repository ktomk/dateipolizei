<?php

declare(strict_types=1);

/*
 * dateipolizei
 *
 * Date: 14.08.17 01:15
 */

namespace Ktomk\DateiPolizei\Fs;

use Ktomk\DateiPolizei\String\Matcher\PcreMatcher;
use PHPUnit\Framework\TestCase;

class RIgnoreFilterTest extends TestCase
{
    function testCreation()
    {
        $iterator = new RDirIter("");
        $filter = new RIgnoreFilter($iterator, new PcreMatcher());
        $this->assertInstanceOf(RIgnoreFilter::class, $filter);
    }

    function testFiltering()
    {
        $iterator = new RDirIter(__DIR__ . '/..');
        $filter = new RIgnoreFilter($iterator, new PcreMatcher(false));

        /** @var \RecursiveIteratorIterator|RIgnoreFilter $rIter */
        $rIter = new \RecursiveIteratorIterator($filter, \RecursiveIteratorIterator::SELF_FIRST);
        $rIter->rewind();
        $this->assertTrue($rIter->current()->isDir(), 'is a directory');
        $this->assertTrue($rIter->accept(), 'is accepted');
        $rIter->next(); # move to file
        $this->assertTrue($rIter->current()->isFile(), 'is a file');
        $this->assertTrue($rIter->accept(), 'is accepted');

        $filter = new RIgnoreFilter($iterator, new PcreMatcher(true));
        $rIter = new \RecursiveIteratorIterator($filter, \RecursiveIteratorIterator::SELF_FIRST);
        $rIter->rewind();
        $this->assertTrue($rIter->current()->isFile(), 'is a file');
        $this->assertTrue($rIter->accept(), 'is accepted');
    }
}
