<?php

/*
 * dateipolizei
 *
 * Date: 07.08.17 18:10
 */

namespace Ktomk\DateiPolizei\CliTest;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Ktomk\DateiPolizei\CliTest\XDebugHelper
 */
class XDebugHelperTest extends TestCase
{
    function testCreation()
    {
        $helper = new XDebugHelper();
        $this->assertInstanceOf(XDebugHelper::class, $helper);
    }

    /**
     * @param string $method
     * @param callable $callback
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|XDebugHelper
     */
    private function getSingleFuncMock(string $method, callable $callback)
    {
        $builder = $this->getMockBuilder(XDebugHelper::class);
        $builder->setMethods([$method]);

        $helper = $builder->getMock();
        $helper
            ->expects($this->any())
            ->method($method)
            ->willReturnCallback($callback);

        return $helper;
    }

    function testIniParsing()
    {
        $setSearchResult = function (array $result) use (&$searchResult) {
            $searchResult = $result;
        };
        $helper = $this->getSingleFuncMock('searchIni', function () use (&$searchResult) {
            return $searchResult;
        });

        $setSearchResult([]);
        $this->assertNull($helper->getExtension());

        $setSearchResult([[true, 'fake extension']]);
        $this->assertNull($helper->getExtension());
    }

    function testGetArgsNonLoaded()
    {
        $setIsLoaded = function (bool $loaded) use (&$isLoaded) {
            $isLoaded = $loaded;
        };
        $helper = $this->getSingleFuncMock('isLoaded', function () use (&$isLoaded) {
            return $isLoaded;
        });

        $helper::$instance = null;
        $setIsLoaded(false);
        $this->assertSame("", $helper->getArgs());
    }

    function testUnreadableIniFile()
    {
        $setIniPathnames = function (array $pathnames) use (&$iniPathnames) {
            $iniPathnames = $pathnames;
        };
        $helper = $this->getSingleFuncMock('getIniPathnames', function () use (&$iniPathnames) {
            return $iniPathnames;
        });

        $setIniPathnames(['/this/file/does/not/exists/not/at/all/99387938DsXfaSf94J83.ini']);
        $this->assertEquals([], $helper->searchIni());
    }

    /**
     * tests quite a special case for de-quoting and absolute path being non-existing (xdebug) extension file
     */
    function testQuotedAbsoluteNonExistingExtensionPath()
    {
        $setIniPathnames = function (array $pathnames) use (&$iniPathnames) {
            $iniPathnames = $pathnames;
        };
        $helper = $this->getSingleFuncMock('getIniPathnames', function () use (&$iniPathnames) {
            return $iniPathnames;
        });

        $tmpFile = tmpfile();
        fwrite($tmpFile, ';zend_extension="/absolute/path/to/xdebug-extension"');
        $path = stream_get_meta_data($tmpFile)['uri'];
        $setIniPathnames([$path]);
        $this->assertEquals([], $helper->searchIni());
    }
}
