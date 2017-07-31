<?php

/*
 * dateipolizei
 *
 * Date: 23.07.17 13:04
 */

namespace Ktomk\DateiPolizei\Cli;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Ktomk\DateiPolizei\Cli\ArgsTokens
 */
class ArgsTokensTest extends TestCase
{
    public function testCreation()
    {
        $tokens = ArgsTokens::createFromArgs(...[]);
        $this->assertInstanceOf(ArgsTokens::class, $tokens);
    }

    public function testCtor()
    {
        # new with s-expression
        $tokens = new ArgsTokens([[ArgsTokens::T_UTILITY, "foo"]]);
        $this->assertInstanceOf(ArgsTokens::class, $tokens);

        # new with tokens and args
        $tokens = new ArgsTokens([ArgsTokens::T_UTILITY], ["foo"]);
        $this->assertInstanceOf(ArgsTokens::class, $tokens);
    }

    public function testIteration()
    {
        $tokens = new ArgsTokens([ArgsTokens::T_UTILITY], ["foo"]);
        $count = 0;
        foreach ($tokens as $key => [$token, $arg]) {
            ++$count;
            $this->assertEquals(ArgsTokens::T_UTILITY, $token);
            $this->assertSame("foo", $arg);
        }
        $this->assertEquals(1, $count);
    }

    public function testSeeking()
    {
        $tokens = new ArgsTokens([ArgsTokens::T_UTILITY], ["foo"]);
        $tokens->seek(0);
        $this->addToAssertionCount(1);

        try {
            $tokens->seek(1);
            $this->fail('An expected exception was not thrown');
        } catch (\OutOfBoundsException $ex) {
            $this->addToAssertionCount(1);
        }
    }

    /**
     * note: this test shows that there is a whole party going on in that method,
     *       one idea for reduction is to make argument and operand the same
     *       token [done].
     *       throwing exceptions has been identified as one smell (and "patched"
     *       with optional parameter, urgs) perhaps a future parser object
     *       with a "strict mode" can help here.
     *       a parser object when more practical experience is gained might
     *       lighten things up here. so far, practical useful things are added
     *       to ArgsTokens via methods like @see ArgsTokens::consume() or
     *       @see ArgsTokens::consumeRemainingArguments().
     */
    public function testConsume()
    {
        $tokens = ArgsTokens::createFromArgs('foo', '--option', 'argument');

        # consume first
        $this->assertSame('foo', $tokens->consume());
        $this->assertFalse($tokens->valid(), 'index moves before the start');

        # consume last
        $tokens->seek(1);
        $this->assertSame('argument', $tokens->consume());
        $this->assertTrue($tokens->valid(), 'index moves back to a valid position');
        $tokens->next();
        $this->assertFalse($tokens->valid(), 'invalid position after next');

        # default return null on invalid position
        $this->assertNull(
            $tokens->consume(),
            'return null on invalid position by default'
        );

        # throw exception on invalid position
        try {
            $tokens->consume(null, true);
            $this->fail('An expected exception was not thrown');
        } catch (\OutOfBoundsException $ex) {
            $this->addToAssertionCount(1);
        }

        # throw exception on token mismatch
        $tokens->seek(0);
        $this->assertSame($tokens::T_OPTION, $tokens->current()[0]);

        $this->assertNull(
            $tokens->consume($tokens::T_UTILITY),
            'return null on token mismatch by default'
        );

        try {
            $tokens->consume($tokens::T_UTILITY, true);
            $this->fail('An expected exception was not thrown');
        } catch (\BadMethodCallException $ex) {
            $this->addToAssertionCount(1);
        }

    }

    public function testConsumeRemainingArgOps()
    {
        $tokens = ArgsTokens::createFromArgs('foo', '--option', 'argument', '--', 'operand');

        # consume the first two tokens
        $this->assertTrue($tokens->valid());
        $tokens->consume();
        $tokens->next();
        $this->assertTrue($tokens->valid());
        $tokens->consume();
        $tokens->next();
        $this->assertTrue($tokens->valid());

        $operands = $tokens->consumeRemainingArguments();
        $expected = ['argument', 'operand'];
        $this->assertEquals($expected, $operands);

        # test same but w/o consuming first
        $tokens = ArgsTokens::createFromArgs('foo', '--option', 'argument', '--', 'operand');
        $tokens->next();
        $tokens->next();
        $operands = $tokens->consumeRemainingArguments();
        $this->assertEquals($expected, $operands);

        $tokens = ArgsTokens::createFromArgs('foo', '--option', 'argument', '--', 'operand');
        try {
            $tokens->consumeRemainingArguments();
            $this->fail('An expected exception was not thrown');
        } catch (\BadMethodCallException $ex) {
            $this->addToAssertionCount(1);
        }
    }

}
