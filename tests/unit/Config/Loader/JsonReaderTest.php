<?php

/*
 * dateipolizei
 *
 * Date: 11.08.17 20:13
 */

namespace Ktomk\DateiPolizei\Config\Loader;

use Ktomk\DateiPolizei\Config;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Ktomk\DateiPolizei\Config\Loader\JsonReader
 */
class JsonReaderTest extends TestCase
{
    function testCreation()
    {
        $reader = new JsonReader("");
        $this->assertInstanceOf(JsonReader::class, $reader);

        $reader = JsonReader::create("");
        $this->assertInstanceOf(JsonReader::class, $reader);
    }

    function testComposition()
    {
        $fileTypesPath = __DIR__ . '/../../../../data/file-types.json';
        $setsPath = __DIR__ . '/../../../../data/sets.json';

        $config = new Config();
        JsonReader::create('data://,{}')
            ->read($config);
        JsonReader::create($fileTypesPath)
            ->merge($config, "file-types");
        JsonReader::create($setsPath)
            ->merge($config, "sets");

        $actual = $config->toArray();
        $expected = [
            'sets' => JsonReader::create($setsPath)->toArray(),
            'file-types' => JsonReader::create($fileTypesPath)->toArray(),
        ];
        $this->assertEquals($expected, $actual);
    }

    /**
     * @expectedException \RuntimeException
     */
    function testInvalidFile()
    {
        $reader = JsonReader::create("");
        $reader->toArray();

    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Failed to parse JSON
     */
    function testInvalidJson()
    {
        $reader = JsonReader::create("data://,{");
        $reader->toArray();

    }
}
