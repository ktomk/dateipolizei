<?php

declare(strict_types=1);

/*
 * dateipolizei
 *
 * Date: 14.08.17 22:58
 */

namespace Ktomk\DateiPolizei\String\Matcher;

use PHPUnit\Framework\TestCase;

class CallbackMatcherTest extends TestCase
{
    function testCreation(): void
    {
        $matcher = new CallbackMatcher('in_array');
        $this->assertInstanceOf(CallbackMatcher::class, $matcher);
    }

    function testMatch(): void
    {
        $empty = function (string $string) {
            return empty($string);
        };

        $matcher = new CallbackMatcher($empty);
        $this->assertTrue($matcher->match(''));
        $this->assertTrue($matcher->match('0'));
        $this->assertFalse($matcher->match('false'));
    }

    function testNonBoolCallbackReturnTriggersAssertion()
    {
        if ('1' !== ini_get('zend.assertions')) {
            $this->markTestSkipped('zend.assertions must be "1"');
        }

        $this->expectException(
            ('1' === ini_get('assert.exception'))
                ? 'AssertionError'
                : 'PHPUnit\Framework\Error\Warning'
        );

        $matcher = new CallbackMatcher('strlen');
        $matcher->match('');
        $this->fail('an expected assertion did not trigger');
    }
}
