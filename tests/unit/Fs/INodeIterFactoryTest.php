<?php

/*
 * dateipolizei
 *
 * Date: 09.08.17 20:31
 */

namespace Ktomk\DateiPolizei\Fs;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Ktomk\DateiPolizei\Fs\INodeIterFactory
 */
class INodeIterFactoryTest extends TestCase
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


    function providePathStrings()
    {
        return [
            'file' => [__FILE__],
            'dir' => [__DIR__],
        ];
    }

    /**
     * @dataProvider providePathStrings
     *
     * @param string $path
     */
    function testCreateFromString(string $path)
    {
        $actual = $this->factory->getIterator($path);
        $this->assertInstanceOf(INodeIter::class, $actual);
    }

    /**
     * @expectedException \UnexpectedValueException
     */
    function testCreateFromEmptyString()
    {
        $this->factory->getIterator('');
    }
}
