<?php

declare(strict_types=1);

/*
 * dateipolizei
 *
 * Date: 04.08.17 23:35
 */

namespace Ktomk\DateiPolizei\String;

use PHPUnit\Framework\TestCase;

class InPathMatcherTest extends TestCase
{
    function testCreation()
    {
        $matcher = new InPathMatcher();
        $this->assertInstanceOf(InPathMatcher::class, $matcher);
    }

    function testAddSegments()
    {
        $matcher = new InPathMatcher();
        $matcher->addSegments('foo', 'bar');
        $this->assertTrue($matcher->match('foo/bar/baz'));
        $this->assertTrue($matcher->match('foo/bag/bag'));
        $this->assertTrue($matcher->match('fog/bar/bag'));
        $this->assertFalse($matcher->match('fog/bag/bug'));
    }

    function testAddingSameSegmentTwice()
    {
        // adding the same segment twice must only keep it once
        $matcher = new InPathMatcher();
        $matcher->addSegments('foo', 'foo');
        $this->assertEquals(['foo'], $matcher->getSegments());

        $matcher->addSegments('foo', 'bar', 'bar');
        $this->assertEquals(['bar', 'foo'], $matcher->getSegments());
    }

    function testAddSimpleSegment()
    {
        $matcher = new InPathMatcher();
        $matcher->addSegment("foo");
        $this->assertFalse($matcher->match("not/in/here"), 'match n/a');
        $this->assertFalse($matcher->match("bar-foo"), 'match n/a 2');
        $this->assertFalse($matcher->match("foo-bar"), 'match n/a 3');
        $this->assertTrue($matcher->match("foo/bar/baz"), 'at the beginning');
        $this->assertTrue($matcher->match("bar/foo/baz"), 'in the middle');
        $this->assertTrue($matcher->match("bar/baz/foo"), 'at the end');
    }

    function testCompoundSegment()
    {
        $matcher = new InPathMatcher();
        $matcher->addSegment("fo/o");
        $this->assertFalse($matcher->match("not/in/here"), 'match n/a');
        $this->assertFalse($matcher->match("bar-fo/o"), 'match n/a 2');
        $this->assertFalse($matcher->match("fo/o-bar"), 'match n/a 3');
        $this->assertTrue($matcher->match("fo/o/bar/baz"), 'at the beginning');
        $this->assertTrue($matcher->match("bar/fo/o/baz"), 'in the middle');
        $this->assertTrue($matcher->match("bar/baz/fo/o"), 'at the end');
    }

    function testEmptySegment()
    {
        $matcher = new InPathMatcher();
        $matcher->addSegment("");
        $this->assertTrue($matcher->match(""), 'empty rule matches always');
    }

    function testMatching()
    {
        $matcher = new InPathMatcher();
        // no rules match nothing
        $this->assertFalse($matcher->match(""), 'no rules do not match');

    }
}
