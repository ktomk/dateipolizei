<?php

declare(strict_types=1);

/*
 * dateipolizei
 *
 * Date: 11.08.17 20:31
 */

namespace Ktomk\DateiPolizei\Config\Loader;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Ktomk\DateiPolizei\Config\Loader\ArrayMerger
 */
class ArrayMergerTest extends TestCase
{
    /**
     * @var ArrayMerger
     */
    private $subject;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        parent::setUp();
        $this->subject = new ArrayMerger();
    }

    function testCreation()
    {
        $this->assertInstanceOf(ArrayMerger::class, $this->subject);
    }

    function provideNodesForChildrenTest()
    {
        return [
            'string' => ["", false],
            'null' => [null, false],
            'list' => [range(0, 10), false],
            'map' => [["foo" => "bar", "bar" => "foo"], true],
        ];
    }

    /**
     * @dataProvider provideNodesForChildrenTest
     * @param $node
     * @param $expected
     */
    function testHasChildren($node, $expected): void
    {
        $actual = $this->subject->hasChildren($node);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @expectedException \BadMethodCallException
     */
    function testNonRecursiveThrowsException()
    {
        $this->subject->mergeNonRecursive([],[], false);
    }

    /**
     * Test that merging lists works with force (and not w/o),;lists are never
     * recursive by definition
     */
    function testMergeMapIntoList()
    {
        $map = ["bar" => "foo"];
        $list = range(1, 4);
        try {
            $this->subject->merge($map, $list);
            $this->fail('an expected exception was not thrown');
        } catch (\RuntimeException $ex) {
            $this->addToAssertionCount(1);
        }

        $actual = $this->subject->merge($map, $list, true);
        $this->assertEquals($map, $actual);
    }

    function testMergeMapIntoEmptyMap()
    {
        $map = ["bar" => "foo"];
        $empty = [];
        $actual = $this->subject->merge($map, $empty);
        $this->assertEquals($map, $actual);
    }

    function testMergeListIntoMap()
    {
        $map = ["bar" => "foo"];
        $list = range(1, 4);
        try {
            $this->subject->merge($list, $map);
            $this->fail('an expected exception was not thrown');
        } catch (\RuntimeException $ex) {
            $this->addToAssertionCount(1);
        }

        $actual = $this->subject->merge($list, $map, true);
        $this->assertEquals($list, $actual);
    }

    function testMergeListIntoList()
    {
        $actual = $this->subject->merge(range(2, 7), range(0, 3));
        $this->assertEquals(range(0, 7), $actual);
    }

    function testMergeRecursiveNewMapEntry()
    {
        $map = ["bar" => ["bar" => "foo"]];
        $expected = [
            "bar" => ["bar" => "foo"],
            "foo" => ["foo" => "bar"],
        ];
        $actual = $this->subject->merge($map, ["foo" => ["foo" => "bar"]]);
        $this->assertEquals($expected, $actual, "map is extended by new map entries");
    }

    function testMergeRecursiveValueIsMergedIntoList()
    {
        $map = ["bar" => "foo"];
        $expected = [
            "bar" => ["bar", "foo"],
        ];
        $actual = $this->subject->merge($map, ["bar" => ["bar"]]);
        $this->assertEquals($expected, $actual);
    }

    function testMergeRecursiveValueIsOverwritingMap()
    {
        $map = ["bar" => "foo"];
        try {
            $this->subject->merge($map, ["bar" => ["foo" => "bar"]]);
            $this->fail('An expected exception was not thrown');
        } catch (\UnexpectedValueException $ex) {
            $this->addToAssertionCount(1);
        }

        $actual = $this->subject->merge($map, ["bar" => ["foo" => "bar"]], true);
        $this->assertEquals($map, $actual);
    }

    function testMergeRecursiveOverwriteValueWithValue()
    {
        $map = ["bar" => "old"];
        $actual = $this->subject->merge($map, ["bar" => "old"]);
        $this->assertEquals($map, $actual);
    }

    function testMergeRecursiveListIntoValue()
    {
        $map = ["bar" => ["bar", "foo"]];

        $expected = ["bar" => ["baz", "bar", "foo"]];
        $actual = $this->subject->merge($map, ["bar" => "baz"]);
        $this->assertEquals($expected, $actual, "new list with previous value");

        $actual = $this->subject->merge($map, ["bar" => "bar"]);
        $this->assertEquals($map, $actual, "no duplicate values");
    }

    function testMergeRecursiveTwoValuesCreateList()
    {
        $expected = ["bar" => ["bar", "foo"]];
        $actual = $this->subject->merge(["bar" => "foo"], ["bar" => "bar"]);
        $this->assertEquals($expected, $actual, "new list with two values");
    }


    function testMergeRecursiveMapIntoValue()
    {
        $map = ["bar" => ["bar" => "foo"]];

        try {
            $this->subject->merge($map, ["bar" => null]);
            $this->fail('An expected exception was not thrown');
        } catch (\RuntimeException $ex) {
            $this->addToAssertionCount(1);
        }

        $actual = $this->subject->merge($map, ["bar" => null], true);
        $this->assertEquals($map, $actual);
    }

    function testMergeRecursiveMapIntoMap()
    {
        $map = ["bar" => ["bar" => "foo"]];
        $actual = $this->subject->merge($map, $map);
        $this->assertEquals($map, $actual);

        $map = ["bar" => ["bar" => "foo"]];
        $expected = ["bar" => ["foo" => "bar", "bar" => "foo"]];
        $actual = $this->subject->merge($map, ["bar" => ["foo" => "bar"]]);
        $this->assertEquals($expected, $actual);
        $this->assertSame($expected, $actual, 'check order that new elements are added after existing ones');
    }

    function testMergeRecursiveListIntoList()
    {
        $list = ["bar" => range(0, 2)];
        $actual = $this->subject->merge($list, $list);
        $this->assertEquals($list, $actual);

        $list = ["bar" => range(3, 7)];
        $expected = ["bar" => range(0, 7)];
        $actual = $this->subject->merge($list, ["bar" => range(0, 4)]);
        $this->assertEquals($expected, $actual);
        $this->assertSame($expected, $actual, 'check order that new elements are added after existing ones');
    }


    function testMergeRecursiveMapIntoList()
    {
        $map = ["bar" => ["bar" => "foo"]];
        try {
            $this->subject->merge($map, ["bar" => range(0, 2)]);
            $this->fail('An expected exception was not thrown');
        } catch (\UnexpectedValueException $ex) {
            $this->addToAssertionCount(1);
        }

        $actual = $this->subject->merge($map, ["bar" => range(0, 2)], true);
        $this->assertEquals($map, $actual);
    }

    function testMergeRecursive()
    {
        $array = [
            "foo" => [2, 3, 4],
            "baz" => ["bar" => ["foo" => "bar"]]
        ];
        $into = [
            "bar" => "foo",
            "foo" => 1,
            "baz" => ["bar" => ["bar" => "foo"]]
        ];
        $expected = [
            "bar" => "foo",
            "foo" => [1, 2, 3, 4],
            "baz" => ["bar" =>
                [
                    "bar" => "foo",
                    "foo" => "bar"
                ]
            ]
        ];
        $actual = $this->subject->merge($array, $into);
        $this->assertEquals($expected, $actual);
    }

    function testIsMap()
    {
        $this->assertTrue($this->subject->isMap([]), 'empty array is a map');
        $this->assertTrue($this->subject->isMap(["foo" => "bar"]), 'assoc array is a map');
        $this->assertTrue($this->subject->isMap(["foo" => "bar", "test"]), 'assoc array is a map');
        $this->assertFalse($this->subject->isMap(range(1, 3)), 'list array is not a map');
    }

    function testIsList()
    {
        $this->assertTrue($this->subject->isList([]), 'empty array is a list');
        $this->assertTrue($this->subject->isList(range(1, 3)), 'list array is a list');
        $this->assertFalse($this->subject->isList(["foo" => "bar"]), 'assoc array is not a list');
        $this->assertFalse($this->subject->isList(["test", "foo" => "bar"]), 'assoc array is not a list');
    }
}
