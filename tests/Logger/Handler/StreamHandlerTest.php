<?php
/**
 * Created by JetBrains PhpStorm.
 * User: dawen
 * Date: 21.05.13
 * Time: 19:13
 * To change this template use File | Settings | File Templates.
 */

class StreamHandlerTest extends \PHPUnit_Framework_TestCase
{
    const TEST_PATH = '/tmp';
    const TEST_FILE = '/DawenHandlerUnitTest.log';

    private $oLogger = null;

    private function createHandler($sFileName, $sTimeStampFormat = null)
    {
        if(!is_writable(self::TEST_PATH))
        {
            $this->markTestSkipped('/tmp is not writable');
        }

        $_sFilePath = self::TEST_PATH.$sFileName;

        return new \Dawen\Logger\Handler\StreamHandler(
            $_sFilePath,
            $sTimeStampFormat);
    }

    private function deleteFile($sFileName)
    {
        $_sFilePath = self::TEST_PATH.$sFileName;
        if(is_file($_sFilePath))
        {
            unlink(self::TEST_PATH.$sFileName);
        }
    }

    protected function setUp()
    {
        parent::setUp();
        $this->oLogger = $this->createHandler(self::TEST_FILE);
    }

    protected function tearDown()
    {
        parent::tearDown();
        $this->deleteFile(self::TEST_FILE);
    }

    public function testInstance()
    {
        $this->assertInstanceOf('Dawen\Logger\Handler\HandlerInterface',$this->oLogger);
    }

}