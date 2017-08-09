<?php

/*
 * dateipolizei
 *
 * Date: 09.08.17 19:09
 */

namespace Ktomk\DateiPolizei\Fs;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Ktomk\DateiPolizei\Fs\PathIter
 */
class PathIterTest extends TestCase
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
        $iter = new PathIter($this->factory,__FILE__);
        $this->assertInstanceOf(PathIter::class, $iter);
    }

    function testFile()
    {
        $iter = new PathIter($this->factory,__FILE__);
        $this->assertSame(__FILE__, $iter->getSubPathname());
        $this->assertNotNull($iter->current());
    }

    function testDir()
    {
        $iter = new PathIter($this->factory,__DIR__);
        $subPathname = $iter->getSubPathname();
        $this->assertNotEquals(__DIR__, $subPathname);
        $this->assertFileExists(__DIR__ . '/' . $subPathname);
        $this->assertNotNull($iter->current());
    }
}
