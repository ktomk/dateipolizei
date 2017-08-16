<?php declare(strict_types=1);

/*
 * dateipolizei
 *
 * Date: 11.08.17 20:31
 */

namespace Ktomk\DateiPolizei\Config\Loader;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Ktomk\DateiPolizei\Config\Loader\ArrayUtil
 */
class ArrayUtilTest extends TestCase
{
    /**
     * @var ArrayUtil
     */
    private $subject;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        parent::setUp();
        $this->subject = new ArrayUtil();
    }

    function testCreation()
    {
        $this->assertInstanceOf(ArrayUtil::class, $this->subject);
    }

    function testStripKeyRecursive()
    {
        $data = [
            "_comment" => "we want to strip all of these",
            "section" => [
                "comment-only" => [
                    "_comment" => "",
                ],
                "both" => [
                    "_comment" => "this section has a comment",
                    "foo" => "bar",
                ],
                "just a string",
                "list" => [
                    "_comment",
                    "should keep",
                ]

            ]
        ];

        $expected = [
            "section" => [
                "comment-only" => [], # now empty
                'both' => [
                    'foo' => 'bar',
                ],
                'just a string',
                'list' => ["_comment", "should keep"],
            ],
        ];
        $actual = $this->subject->stripKeyRecursive($data, "_comment");
        $this->assertEquals($expected, $actual);
    }

    function testMergeRecursive()
    {
        $this->assertEquals([], $this->subject->mergeRecursive([], []));
    }
}
