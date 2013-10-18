<?php
/**
 * Created by JetBrains PhpStorm.
 * User: da-wen
 * Date: 21.05.13
 * Time: 19:13
 * To change this template use File | Settings | File Templates.
 */

class StreamFormatterHandlerTest extends \PHPUnit_Framework_TestCase
{
    const TEST_PATH = '/tmp';
    const TEST_FILE = '/DawenFormatterHandlerUnitTest.log';

    const LEVEL_DEBUG = 100;
    const LEVEL_ERROR = 400;

    /** @var null|\Dawen\Logger\Handler\StreamFormatterHandler */
    private $oLogger = null;

    private function createHandler($iLevel = self::LEVEL_DEBUG,$sFileName, $sTimeStampFormat = null)
    {
        if(!is_writable(self::TEST_PATH))
        {
            $this->markTestSkipped('/tmp is not writable');
        }

        $_sFilePath = self::TEST_PATH.$sFileName;

        return new \Dawen\Logger\Handler\StreamFormatterHandler(
            $iLevel,
            $_sFilePath);
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
        $this->oLogger = $this->createHandler(self::LEVEL_ERROR, self::TEST_FILE);
    }

    protected function tearDown()
    {
        parent::tearDown();
        $this->deleteFile(self::TEST_FILE);
    }

    public function testInstance()
    {
        $this->assertInstanceOf('Dawen\Logger\Handler\HandlerFormatterInterface',$this->oLogger);
        $this->assertInstanceOf('Dawen\Logger\Handler\HandlerInterface',$this->oLogger);
    }

    public function testWrongHandleParam()
    {
        $_iData = 100;
        try
        {
            $_bHandle = $this->oLogger->handle($_iData);
        }
        catch(\Exception $_oException)
        {
            return;
        }

        $this->fail('An expected Exeption has not been raised.');
    }

    public function testMissingParams()
    {
        try
        {
            $_aData = array('iLevelss' => self::LEVEL_DEBUG, 'sTimestamp' => date('Y-m-d H:i:s'));
            $_bHandle = $this->oLogger->handle($_aData);
        }
        catch(\Exception $_oException)
        {
            return;
        }

        $this->fail('An expected Exeption has not been raised. Missing parameters');
    }


    public function testLevelError()
    {
        $_aData = array('iLevel' => self::LEVEL_DEBUG, 'sTimestamp' => date('Y-m-d H:i:s'));
        $_bHandle = $this->oLogger->handle($_aData);
        $this->assertFalse($_bHandle);
    }

    public function testNullResource()
    {
        $_sLogString = 'testing';

        $_oLogger = $this->createHandler(
            self::LEVEL_DEBUG,
            null);

        $_aData = array(
            'iLevel' => self::LEVEL_DEBUG,
            'sTimestamp' => date('Y-m-d H:i:s')
        );

        $_oException = null;
        try
        {
            $_oLogger->handle($_aData);
        }
        catch(\Exception $_oException)
        {
            //do nothing
        }
        $this->assertInstanceOf('\UnexpectedValueException',$_oException);
    }

    public function testWrite()
    {
        $_aData = array(
            'iLevel' => self::LEVEL_ERROR,
            'sTimestamp' => date('Y-m-d H:i:s'),
            'sLevel' => 'debug',
            'sLoggerName' => 'handlertest',
            'sMessage' => 'this is a debug message',
            'aContext' => array(
                'var1' => 'val1',
                'var2' => 2
            )
        );
        $_bHandled = $this->oLogger->handle($_aData);
        $this->assertTrue($_bHandled);

        $_rHandle = fopen(self::TEST_PATH.self::TEST_FILE, "r");
        $_aContents = array();
        while (($_sBuffer = fgets($_rHandle, 4096)) !== false) {
            $_aContents[] = $_sBuffer;
        }
        fclose($_rHandle);

        $this->assertCount(1,$_aContents);
        $this->assertContains($_aData['sLoggerName'].'.'.$_aData['sLevel'], $_aContents[0]);
        $this->assertContains($_aData['sMessage'], $_aContents[0]);
        $this->assertContains('['.json_encode($_aData['aContext']).']', $_aContents[0]);

    }

    public function testTimestampFormat()
    {
        $_aData = array(
            'iLevel' => self::LEVEL_ERROR,
            'sTimestamp' => date('Y-m-d H:i:s'),
            'sLevel' => 'debug',
            'sLoggerName' => 'handlertest',
            'sMessage' => 'this is a debug message',
            'aContext' => array(
                'var1' => 'val1',
                'var2' => 2
            )
        );
        $_sTimeStampFormat = 'YmdHis';
        $_oHandler = $this->createHandler(self::LEVEL_DEBUG, self::TEST_FILE, $_sTimeStampFormat);
        $_bHandled = $_oHandler->handle($_aData);
        $this->assertTrue($_bHandled);

        $_rHandle = fopen(self::TEST_PATH.self::TEST_FILE, "r");
        $_aContents = array();
        while (($_sBuffer = fgets($_rHandle, 4096)) !== false) {
            $_aContents[] = $_sBuffer;
        }
        fclose($_rHandle);


        $this->assertCount(1,$_aContents);
        $this->assertContains($_aData['sLoggerName'].'.'.$_aData['sLevel'], $_aContents[0]);
        $this->assertContains('[' . $_aData['sTimestamp'] . ']', $_aContents[0]);
        $this->assertContains($_aData['sMessage'], $_aContents[0]);
        $this->assertContains('['.json_encode($_aData['aContext']).']', $_aContents[0]);

    }

    public function testDefaultFormatter()
    {
        $_oFormatter = $this->oLogger->getFormatter();
        $this->assertInstanceOf('Dawen\Logger\Formatter\FormatterInterface', $_oFormatter);
    }

    public function testSetFormatterInstanceError()
    {
        $_oException = null;
        try
        {
            $this->oLogger->setFormatter(new \StdClass());
        }
        catch(\Exception $_oException)
        {
            //do nothing
        }
        $this->assertInstanceOf('\Exception',$_oException);

    }

    public function testSetFormatterInstance()
    {
        $_oException = null;
        try
        {
            $_oMock = $this->getMock('Dawen\Logger\Formatter\FormatterInterface', array('format'));
            $this->oLogger->setFormatter($_oMock);
        }
        catch(\Exception $_oException)
        {
            //do nothing
        }
        $this->assertNull($_oException);

    }

}