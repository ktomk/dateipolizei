<?php

/*
 * dateipolizei
 *
 * Date: 01.08.17 21:42
 */

namespace Ktomk\DateiPolizei\Fs;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Ktomk\DateiPolizei\Fs\RRDirIter
 */
class RRDirIterTest extends TestCase
{
    public function testIteration()
    {
        $iter = new RDirIter(__DIR__);
        $rIter = new RRDirIter($iter);

        $array = iterator_to_array($rIter);

        $this->assertInternalType('array', $array);
        $this->assertGreaterThan(1, count($array));
    }

    public function testGetSubPathname()
    {
        $iter = new RDirIter(__DIR__);
        $rIter = new RRDirIter($iter);

        foreach ($rIter as $iNode) {
            $actual = $rIter->getSubPathname();
            $this->assertInternalType('string', $actual);
            $this->assertGreaterThan(10, strlen($actual));
            break;
        }

        if (!isset($iNode)) {
            $this->fail('Necessary iteration not done');
        }
    }
}
