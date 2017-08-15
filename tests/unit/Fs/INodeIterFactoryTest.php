<?php

/*
 * dateipolizei
 *
 * Date: 09.08.17 20:31
 */

namespace Ktomk\DateiPolizei\Fs;

use Ktomk\DateiPolizei\String\CallbackMatcher;
use Ktomk\DateiPolizei\String\PatternMatcher;
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

        $this->factory = new INodeIterFactory(null);
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

    function testWithIgnore()
    {
        $patterns = [
            basename(__DIR__),
        ];
        $matcher = new PatternMatcher();
        $matcher->addPatterns($patterns);

        $factory = new INodeIterFactory($matcher);
        $iter = $factory->getIterator(__DIR__ . '/..');
        $array = iterator_to_array($iter);
        $filter = preg_grep(
            sprintf('~^%s/../Fs/~', preg_quote(__DIR__)),
            $array
        );
        $this->assertCount(0, $filter);
        $filter = preg_grep(
            sprintf('~^%s/../.*/~', preg_quote(__DIR__)),
            $array
        );
        $this->assertGreaterThan(0, count($filter), 'cross-check');
    }

    function testIgnoreSetter()
    {
        $factory = new INodeIterFactory();
        $iter = $factory->getIterator(__DIR__ . '/..');
        $array = iterator_to_array($iter);
        $this->assertGreaterThan(0, count($array));


        $factory->setIgnore(new CallbackMatcher(function(string $filename) {
            throw new \BadMethodCallException('For testing');
        }));
        $iter = $factory->getIterator(__DIR__ . '/..');
        try {
            iterator_to_array($iter);
            $this->fail('An expected exception was not thrown');
        } catch (\BadMethodCallException $ex) {
            $this->addToAssertionCount(1);
        }

        $factory->setIgnore(null);
        $iter = $factory->getIterator(__DIR__ . '/..');
        $array = iterator_to_array($iter);
        $this->assertGreaterThan(0, count($array));
    }
}
