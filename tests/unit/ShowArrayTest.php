<?php

/*
 * dateipolizei
 *
 * Date: 25.07.17 21:43
 */

namespace Ktomk\DateiPolizei;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Ktomk\DateiPolizei\ShowArray
 */
class ShowArrayTest extends TestCase
{
    public function testCreation()
    {
        $show = ShowArray::create([
            'dir' => true,
            'extension' => true,
            'file' => true,
            'link' => true,
            'summary' => true,
            'target' => true,
        ]);
        $this->assertInstanceOf(ShowArray::class, $show);
        $this->assertTrue($show['dir']);
        $this->assertTrue($show['d']);
    }

    public function testThatTheseAreSet()
    {
        $show = ShowArray::create(['dir' => true, 'files' => true]);
        $this->assertFalse($show->areThese(""));
        $this->assertTrue($show->areThese('fd'));
    }

    public function testThatAnyIsSet()
    {
        $show = ShowArray::create(['dir' => true, 'files' => false]);
        $this->assertFalse($show->isAny(""));
        $this->assertFalse($show->isAny("f"));
        $this->assertTrue($show->isAny('fd'));
    }

    public function testSetAll()
    {
        $show = ShowArray::create(['dir' => true, 'files' => true]);
        $this->assertInstanceOf(ShowArray::class, $show->setAll(false));
        $this->assertFalse($show['dir']);
        $this->assertFalse($show['files']);
    }

    public function testSetChars()
    {
        $show = ShowArray::create(['dir' => true, 'files' => true]);
        $show->setChars('f', false);
        $this->assertFalse($show['files']);
        $this->assertTrue($show['dir']);
    }

    public function testExistentOffset()
    {
        $show = ShowArray::create(['dir' => true,]);
        $this->assertTrue($show['dir']);
        $this->assertTrue($show['d']);
        $this->assertTrue($show['d']);
    }

    public function testNonExistentOffset()
    {
        $show = ShowArray::create(['dir' => true,]);

        $this->assertFalse($show->offsetExists('dire'));
        $this->assertFalse($show->offsetExists('erid'));
        $this->assertFalse($show->offsetExists('er'));
        $this->assertFalse($show->offsetExists('e'));
    }

    /**
     * @expectedException \BadMethodCallException
     */
    public function testSetNonExistentOffset()
    {
        $show = ShowArray::create(['dir' => true,]);
        $this->assertTrue($show['dir'] = true);
        $show['foo'] = true;
    }

    /**
     * @expectedException \BadMethodCallException
     */
    public function testGetNonExistentOffset()
    {
        $show = ShowArray::create(['dir' => true,]);
        $this->assertTrue($show['dir']);
        $show['foo'];
    }

    /**
     * @expectedException \BadMethodCallException
     */
    public function testUnset()
    {
        $show = ShowArray::create(['dir' => true,]);
        unset($show['dir']);
    }

    public function testResetToDefaultValues()
    {
        $show = ShowArray::create(['basename' => false, 'dir' => true]);
        $show->setAll(true);
        $this->assertTrue($show['b']);
        $this->assertTrue($show['d']);
        $show->reset();
        $this->assertFalse($show['b']);
        $this->assertTrue($show['d']);
    }
}
