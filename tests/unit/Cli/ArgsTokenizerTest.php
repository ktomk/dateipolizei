<?php

declare(strict_types=1);

/*
 * dateipolizei
 *
 * Date: 23.07.17 10:49
 */

namespace Ktomk\DateiPolizei\Cli;


use PHPUnit\Framework\TestCase;

/**
 * @covers \Ktomk\DateiPolizei\Cli\ArgsTokenizer
 */
class ArgsTokenizerTest extends TestCase
{
    public function testCreation()
    {
        $parser = new ArgsTokenizer();
        $this->assertInstanceOf(ArgsTokenizer::class, $parser);
    }

    public function provideArguments()
    {
        return [
            'happy' => [
                ['utility', '--option', 'command', '-option', 'option-argument', '--', 'operand'],
                [ArgsTokens::T_UTILITY, ArgsTokens::T_OPTION, ArgsTokens::T_ARGUMENT, ArgsTokens::T_OPTION,
                    ArgsTokens::T_ARGUMENT, ArgsTokens::T_DELIMITER, ArgsTokens::T_ARGUMENT]
            ],
            'operands detection' => [
                ['util', '--option', 'argument', 'operand1', 'operand2'],
                [ArgsTokens::T_UTILITY, ArgsTokens::T_OPTION, ArgsTokens::T_ARGUMENT, ArgsTokens::T_ARGUMENT,
                    ArgsTokens::T_ARGUMENT]
            ],
            'operands detection 2' => [
                ['util', 'cmd', '--opt'],
                [ArgsTokens::T_UTILITY, ArgsTokens::T_ARGUMENT, ArgsTokens::T_OPTION]
            ]
        ];
    }

    public function testInitialState()
    {
        $parser = new ArgsTokenizer();

        $this->assertEquals([], $parser->getArguments());
        $this->assertEquals([], $parser->getTokens());
    }

    /**
     * @dataProvider provideArguments
     * @param array $arguments
     */
    public function testAddArguments(array $arguments): void
    {
        $parser = new ArgsTokenizer();
        $parser->addArguments(...$arguments);
        $this->assertEquals($arguments, $parser->getArguments());
    }

    public function testTokenizeEmptyArgs()
    {
        $parser = new ArgsTokenizer();

        $parser->tokenize();
        $this->assertEquals([], $parser->getTokens());
    }

    /**
     * @dataProvider provideArguments
     * @param array $arguments
     * @param array $tokens
     */
    public function testTokenize(array $arguments, array $tokens): void
    {
        $parser = new ArgsTokenizer();
        $parser->addArguments(...$arguments);

        $parser->tokenize();
        $this->assertEquals(
            array_map(null, $arguments, $tokens),
            array_map(null, $parser->getArguments(), $parser->getTokens())
        );

        $this->assertEquals($arguments, $parser->getArguments());
        $this->assertEquals($tokens, $parser->getTokens());
    }
}
