<?php

/*
 * dateipolizei
 *
 * Date: 31.07.17 08:45
 */

namespace Ktomk\DateiPolizei\Fs;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Ktomk\DateiPolizei\Fs\RDirIter
 */
class RDirIterTest extends TestCase
{
    public function testCreation()
    {
        $iter = new RDirIter(__DIR__);
        $this->assertInstanceOf(RDirIter::class, $iter);
    }

    public function testIteration()
    {
        $iter = new RDirIter(__DIR__ . '/..');
        $rIter = new RRDirIter($iter);
        $result = iterator_to_array($rIter);
        $this->assertInternalType('array', $result);
    }

    /**
     * @return \org\bovigo\vfs\vfsStreamDirectory
     */
    private function vfsSetup()
    {
        $root = vfsStream::setup(
            'root',
            null,
            [
                'no-open.ext' => '',
                'dir' => [
                    'sub' => [],
                    'error' => [
                        'file' => 'test',
                    ],
                    'file' => '',

                ],
            ]
        );

        return $root;
    }

    public function testReadableSubDir()
    {
        $root = $this->vfsSetup();
        $iter = new RDirIter($root->url());
        $rIter = new RRDirIter($iter, RRDirIter::SELF_FIRST);

        $result = iterator_to_array($rIter);
        $this->assertInternalType('array', $result, 'iteration works');
        $this->assertEquals(
            [
                'vfs://root/no-open.ext',
                'vfs://root/dir',
                'vfs://root/dir/sub',
                'vfs://root/dir/error',
                'vfs://root/dir/error/file',
                'vfs://root/dir/file',
            ],
            array_keys($result),
            'directories are recursively traversed'
        );

    }

    public function testNonReadableSubDir()
    {
        $root = $this->vfsSetup();
        $iter = new RDirIter($root->url());
        $rIter = new RRDirIter($iter, RRDirIter::SELF_FIRST);

        # make the directory non-readable
        $dir = $root->getChild('dir/error');
        $dir->chmod(0200);

        $result = iterator_to_array($rIter);
        $this->assertInternalType('array', $result, 'iteration works again');
        $this->assertEquals(
            [
                'vfs://root/no-open.ext',
                'vfs://root/dir',
                'vfs://root/dir/sub',
                'vfs://root/dir/error',
                'vfs://root/dir/file',
            ],
            array_keys($result),
            'unreadable directories are not traversed'
        );
    }
}
