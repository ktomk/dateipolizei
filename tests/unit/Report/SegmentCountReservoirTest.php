<?php

declare(strict_types=1);

/*
 * dateipolizei
 *
 * Date: 10.08.17 08:32
 */

namespace Ktomk\DateiPolizei\Report;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Ktomk\DateiPolizei\Report\SegmentCountReservoir
 */
class SegmentCountReservoirTest extends TestCase
{
    function testCreation()
    {
        $segment = new SegmentCountReservoir('.');
        $this->assertInstanceOf(SegmentCountReservoir::class, $segment);
    }

    function testCreationExplicitNullForFlags()
    {
        $segment = new SegmentCountReservoir('.', null);
        $this->assertInstanceOf(SegmentCountReservoir::class, $segment);
    }

    /**
     * @return array
     * @see testMakeOnPrefix
     */
    function provideMakeOnPrefix()
    {
        return [
            'empty, default flags (via null)' => [[], '', '.', null],
            'empty, default flags (via const)' => [[], '', '.', SegmentCountReservoir::FLAGS_DEFAULT],
            'empty, keep' => [[''], '', '.', SegmentCountReservoir::KEEP_EMPTY],
            'extensions' => [['.xml.dist', '.dist'], 'phpunit.xml.dist', '.'],
        ];
    }

    /**
     * @dataProvider provideMakeOnPrefix
     * @param array $expected
     * @param string $subject
     * @param string $prefix
     * @param int|null $flags [optional]
     */
    function testMakeOnPrefix(array $expected, string $subject, string $prefix, ?int $flags = null): void
    {
        $segment = new SegmentCountReservoir('xxx');
        $actual = $segment->makeOnPrefix($subject, $prefix, $flags);
        $this->assertEquals($expected, $actual);
        $this->assertSame($expected, $actual);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    function testEmptyPrefixCausesException()
    {
        $segment = new SegmentCountReservoir('');
        $segment->makeOnPrefix("", '');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    function testEmptyPrefixCausesExceptionOnUtilityMethod()
    {
        $segment = new SegmentCountReservoir('.');
        $segment->makeOnPrefix('', '');

    }

    function testAddWithInternalAssertion()
    {
        $segment = new SegmentCountReservoir('.');
        $segment->add('phpunit.xml.dist');
        $expected = [
            '.xml.dist' => 1,
            '.dist' => 1,
        ];
        $this->assertEquals($expected, iterator_to_array($segment->getSegmentCounts()));

        $expected = [
            '.dist' => [
                ['.xml' => [[], 1]],
                1
            ]
        ];

        $tree = new \ReflectionProperty(SegmentCountReservoir::class, 'tree');
        $tree->setAccessible(true);

        $this->assertEquals($expected, $tree->getValue($segment));

        $segment->add('phpcs.xml.dist');
        $expected = [
            '.xml.dist' => 2,
            '.dist' => 2,
        ];
        $this->assertEquals($expected, iterator_to_array($segment->getSegmentCounts()));

        $expected = [
            '.dist' => [
                ['.xml' => [[], 2]],
                2
            ]
        ];
        $this->assertEquals($expected, $tree->getValue($segment));

    }

    function testSkipRedundantSmaller()
    {
        $segment = new SegmentCountReservoir('.');
        $segment->add('phpunit.xml.dist');
        $segment->add('phpcs.xml.dist');

        $expected = [
            '.xml.dist' => 2,
        ];
        $actual = iterator_to_array($segment->getSegmentCounts(false));
        $this->assertEquals($expected, $actual);

        $segment->add('util.ini.dist');
        $expected = [
            '.xml.dist' => 2,
            '.ini.dist' => 1,
            '.dist' => 3,
        ];
        $actual = iterator_to_array($segment->getSegmentCounts(false));
        $this->assertEquals($expected, $actual);
    }

    function testAddWithCount()
    {
        $segment = new SegmentCountReservoir('.');
        $segment->add('phpunit.xml.dist');
        $expected = [
            '.xml.dist' => 1,
            '.dist' => 1,
        ];
        $this->assertEquals($expected, iterator_to_array($segment->getSegmentCounts()));
        $segment->add('phpcs.xml.dist', 2);
        $expected = [
            '.xml.dist' => 3,
            '.dist' => 3,
        ];
        $this->assertEquals($expected, iterator_to_array($segment->getSegmentCounts()));
    }

    function testFill()
    {
        $reservoir = new SegmentCountReservoir('.');
        $reservoir->fill([
            'phpunit.xml.dist',
            'phpcs.xml.dist',
            'util.ini.dist',
        ]);

        $expected = [
            '.xml.dist' => 2,
            '.ini.dist' => 1,
            '.dist' => 3,
        ];

        $actual = iterator_to_array($reservoir->getSegmentCounts(false));
        $this->assertEquals($expected, $actual, 'base expectation');

        $actual = iterator_to_array($reservoir->getSegmentCounts(true));
        $this->assertEquals($expected, $actual, 'redundant matches');
    }

    function testFillCounts()
    {
        $reservoir = new SegmentCountReservoir('.');
        $reservoir->fillCounts([
            'phpunit.xml.dist' => 3,
            'phpcs.xml.dist' => 5,
            'util.ini.dist' => 1,
        ]);

        $expected = [
            '.xml.dist' => 8,
            '.ini.dist' => 1,
            '.dist' => 9,
        ];

        $actual = iterator_to_array($reservoir->getSegmentCounts(false));
        $this->assertEquals($expected, $actual, 'base expectation');

        $actual = iterator_to_array($reservoir->getSegmentCounts(true));
        $this->assertEquals($expected, $actual, 'redundant matches');
    }}
