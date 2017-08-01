<?php

/*
 * dateipolizei
 * 
 * Date: 31.07.17 08:38
 */

namespace Ktomk\DateiPolizei\Fs;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Ktomk\DateiPolizei\Fs\FileIter
 */
class FileIterTest extends TestCase
{
    public function testCreation()
    {
        $iter = new FileIter(__FILE__);
        $this->assertInstanceOf(FileIter::class, $iter);
    }

    public function testIteration()
    {
        $path = __FILE__;
        $iter = new FileIter($path);
        $array = iterator_to_array($iter);
        $this->assertEquals([$path], $array);

        foreach ($iter as $value) {
            $this->assertEquals((string) $value, $iter->getSubPathname());
        }
    }
}
