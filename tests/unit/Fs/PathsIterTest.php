<?php

/*
 * dateipolizei
 *
 * Date: 09.08.17 18:27
 */

namespace Ktomk\DateiPolizei\Fs;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Ktomk\DateiPolizei\Fs\PathsIter
 */
class PathsIterTest extends TestCase
{
    /**
     * @var INodeIterFactory
     */
    private $factory;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->factory = new INodeIterFactory();
    }

    function testCreation()
    {
        $iter = new PathsIter($this->factory);
        $this->assertInstanceOf(PathsIter::class, $iter);
    }

    function testSinglePath()
    {
        $iter = new PathsIter($this->factory,__FILE__);
        $this->assertSame(__FILE__, $iter->getSubPathname());
        $this->assertInstanceOf(INode::class, $iter->current());
    }

    function testTwoPaths()
    {
        $a = __FILE__;
        $b = __DIR__ . '/FileIterTest.php';
        $iter = new PathsIter($this->factory, $a, $b);

        $this->assertSame($a, $iter->getSubPathname());
        $this->assertInstanceOf(INode::class, $iter->current());

        $iter->next();
        $this->assertSame($b, $iter->getSubPathname(), 'second path');
        $this->assertInstanceOf(INode::class, $iter->current());

        $iter->next();
        $this->assertFalse($iter->valid());
    }
}
