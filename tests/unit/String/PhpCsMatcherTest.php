<?php

/*
 * dateipolizei
 */

namespace Ktomk\DateiPolizei\String;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Ktomk\DateiPolizei\String\PhpCsMatcher
 */
class PhpCsMatcherTest extends TestCase
{
    public function testCreation()
    {
        $matcher = new PhpCsMatcher();
        $this->assertInstanceOf(PhpCsMatcher::class, $matcher);
    }

    public function testPatternValidation()
    {
        $matcher = new PhpCsMatcher();
        $this->assertTrue($matcher::isValid(""));
        $this->assertFalse($matcher::isValid("``"));
    }

    public function testMatching()
    {
        $matcher = new PhpCsMatcher();
        // no rules match everything
        $this->assertTrue($matcher->match("anything"));
        $matcher->addExcludeRule("*.txt$");
        $this->assertFalse($matcher->match("anything.txt"));
        $this->assertTrue($matcher->match("anything.md"));
        $matcher->addIncludeRule("*.md$");
        $this->assertFalse($matcher->match("anything.txt"), 'not included still must be a non-match');
        $matcher->addIncludeRule("*.txt$");
        $this->assertTrue($matcher->match("anything.txt"), 'includes override excludes');
    }

    public function testPatternConversion()
    {
        // handles the windows branching in pattern conversion
        $matcher = new PhpCsMatcher();
        $actual = $matcher::patternPcre("*\\*.txt", '\\');
        $this->assertEquals('`.*\.*.txt`i', $actual);
    }
}
