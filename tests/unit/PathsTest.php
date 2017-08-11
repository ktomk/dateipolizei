<?php

/*
 * dateipolizei
 *
 * Date: 02.08.17 22:27
 */

namespace Ktomk\DateiPolizei;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

/**
 * Class PathsTest
 *
 * @covers \Ktomk\DateiPolizei\Paths
 */
class PathsTest extends TestCase
{
    function testCreation()
    {
        $paths = new Paths();

        $this->assertInstanceOf(Paths::class, $paths);
    }

    function testWorkingDirectoryWoEnv()
    {
        $expected = getcwd();

        $this->assertTrue(putenv('PWD'), 'precondition - unset PWD env var');
        $actual = Paths::getWorkingDirectory();

        $this->assertEquals($expected, $actual);
    }

    function testDefaultIsWorkingDirectory()
    {
        $paths = new Paths();
        $expected = Paths::getWorkingDirectory();
        $actual = iterator_to_array($paths)[0] ?? null;
        $this->assertEquals($expected, $actual);
    }

    function testHasUnreadableNone()
    {
        $paths = new Paths();
        $this->assertNull($paths->hasUnreadable());
    }

    function testSet()
    {
        $paths = new Paths();

        // empty
        $expected = iterator_to_array($paths);
        $paths->set(... (array) null);
        $actual = iterator_to_array($paths);
        $this->assertEquals($expected, $actual);

        // non-empty
        $paths->set('foo');
        $actual = iterator_to_array($paths);
        $this->assertEquals(['foo'], $actual);

    }

    /**
     * @covers \Ktomk\DateiPolizei\Paths::hasUnreadable()
     */
    function testHasUnreadable()
    {
        $paths = new Paths();

        $paths->set(':');
        $actual = $paths->hasUnreadable();
        $this->assertInternalType('string', $actual);

        // create unreadable file via vfsStream
        $root = vfsStream::setup('root', null, ['error.file' => 'test']);
        $file = $root->getChild('error.file');
        $file->chmod(0200);

        // test unreadable (file) path
        $paths->set($file->url());
        $actual = $paths->hasUnreadable();
        $this->assertInternalType('string', $actual);
    }
}
