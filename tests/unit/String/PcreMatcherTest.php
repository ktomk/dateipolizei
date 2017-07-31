<?php

/*
 * dateipolizei
 */

namespace Ktomk\DateiPolizei\String;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Ktomk\DateiPolizei\String\PcreMatcher
 */
class PcreMatcherTest extends TestCase
{
    public function testValidation()
    {
        $matcher = new PcreMatcher();
        $this->assertFalse($matcher::isValid(""));
        $this->assertTrue($matcher::isValid("~~"));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testExceptionOnInvalidPatternAdded()
    {
        $matcher = new PcreMatcher();
        $matcher->addPattern("");
    }

    public function testMatching()
    {
        $matcher = new PcreMatcher();
        $this->assertTrue(
            $matcher->match(""),
            'no rules always matches'
        );
        $matcher->addPattern("~abc~");
        $this->assertFalse(
            $matcher->match(""),
            'the single rule ~abc~ must not match ""'
        );
        $matcher->addPattern("~abc~", true);
        $this->assertTrue(
            $matcher->match(""),
            'the same rule ~abc~ inverted must now match ""'
        );
    }
}
