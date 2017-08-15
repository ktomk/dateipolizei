<?php

declare(strict_types=1);

/*
 * dateipolizei
 *
 * Date: 13.08.17 23:15
 */

namespace Ktomk\DateiPolizei\String;

use PHPUnit\Framework\TestCase;

class PatternMatcherTest extends TestCase
{
    function testCreation()
    {
        $matcher = new PatternMatcher();
        $this->assertInstanceOf(PatternMatcher::class, $matcher);
    }

    function testNoPatternMatchesNot()
    {
        $matcher = new PatternMatcher();
        $this->assertFalse($matcher->match(''));
    }

    function testEmptyPattern()
    {
        $matcher = new PatternMatcher();
        $matcher->addPattern('');
        $this->assertTrue($matcher->match(''));
    }

    function testMatch()
    {
        $matcher = new PatternMatcher();
        $matcher->addPattern('*.md');
        $this->assertTrue($matcher->match('README.md'));
    }

    function testMatchMulti()
    {
        $matcher = new PatternMatcher();
        $matcher->addPattern('*.md;*.txt');
        $matcher->addPattern('*.txt;*.doc');
        $this->assertTrue($matcher->match('README.md'));
        $this->assertTrue($matcher->match('readme.txt'));
        $this->assertFalse($matcher->match(''));
    }

    function testRemovePattern()
    {
        $matcher = new PatternMatcher();
        $matcher->addPattern('*.md');
        $matcher->addPattern('!*.md');
        $this->assertFalse($matcher->match('README.md'));
        $matcher->addPattern('*.md;!*.md');
        $this->assertFalse($matcher->match('README.md'));
    }

    function testAddPatterns()
    {
        $matcher = new PatternMatcher();
        $matcher->addPatterns((array)"*.md;*.txt;");
        $this->assertTrue($matcher->match(''));
        $this->assertFalse($matcher->match('my.doc'));
    }

    function testGetPatterns()
    {
        $matcher = new PatternMatcher();
        $this->assertEquals([], $matcher->getPatterns());
        $matcher->addPattern("*.md;;*.txt");
        $this->assertEquals(['*.md', '', '*.txt'], $matcher->getPatterns());

    }

    function testSemicolonSeparator()
    {
        $matcher = new PatternMatcher();
        $pattern = 'file-w\;.txt';
        $matcher->addPattern($pattern);

        $this->assertFalse($matcher->match($pattern));
        $this->assertTrue($matcher->match('file-w;.txt'));
        $this->assertFalse($matcher->match('file-w.txt'));

        $this->assertEquals([$pattern], $matcher->getPatterns());
    }

    function testNotRemovePattern()
    {
        $matcher = new PatternMatcher();
        $matcher->addPattern('\!*.!!');
        $this->assertTrue($matcher->match('!abc.!!'));
        $this->assertEquals(['!*.!!'], $matcher->getPatterns());
    }

    function testClearPattern()
    {
        $matcher = new PatternMatcher();
        $matcher->addPattern('*.php');
        $this->assertTrue($matcher->match('hello-world.php'));
        $matcher->clearPatterns();
        $this->assertFalse($matcher->match('hello-world.php'));
    }
}
