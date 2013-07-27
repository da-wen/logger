<?php
/**
 * Created by JetBrains PhpStorm.
 * User: dawen
 * Date: 16.05.13
 * Time: 08:17
 * To change this template use File | Settings | File Templates.
 */

class AdvancedSimpleLoggerTest extends \PHPUnit_Framework_TestCase
{
    const TEST_PATH = '/tmp';
    const TEST_FILE = '/DawenAdvancedLoggerUnitTest.log';
    const LOGGER_NAME = 'UnitTestLogger';

    private $aLevels = array(
        \Dawen\Logger\AdvancedLogger::LEVEL_DEBUG     => 'DEBUG',
        \Dawen\Logger\AdvancedLogger::LEVEL_INFO      => 'INFO',
        \Dawen\Logger\AdvancedLogger::LEVEL_NOTICE    => 'NOTICE',
        \Dawen\Logger\AdvancedLogger::LEVEL_WARNING   => 'WARNING',
        \Dawen\Logger\AdvancedLogger::LEVEL_ERROR     => 'ERROR',
        \Dawen\Logger\AdvancedLogger::LEVEL_CRITICAL  => 'CRITICAL',
        \Dawen\Logger\AdvancedLogger::LEVEL_ALERT     => 'ALERT',
        \Dawen\Logger\AdvancedLogger::LEVEL_EMERGENCY => 'EMERGENCY',
    );

    private $aContext = array('context1' => 'val1', 'context2' => 2);
    private $aExtra = array('extra1' => 'val1', 'extra2' => 2);

    private function createLogger($iLogLevel,$sFileName, $sTimeStampFormat = null, $bWithHandler = true)
    {
        if(!is_writable(self::TEST_PATH))
        {
            $this->markTestSkipped('/tmp is not writable');
        }

        $_sFilePath = self::TEST_PATH.$sFileName;

        $_oHandler = new \Dawen\Logger\Handler\StreamHandler($iLogLevel,$_sFilePath);
        $_oLogger = new \Dawen\Logger\AdvancedLogger(self::LOGGER_NAME, $sTimeStampFormat);
        if($bWithHandler)
        {
            $_oLogger->setHandler($_oHandler);
        }

        return $_oLogger;
    }

    private function deleteFile($sFileName)
    {
        $_sFilePath = self::TEST_PATH.$sFileName;
        if(is_file($_sFilePath))
        {
            unlink(self::TEST_PATH.$sFileName);
        }
    }

    public function testInstance()
    {
        $_oLogger = $this->createLogger(
            \Dawen\Logger\AdvancedLogger::LEVEL_DEBUG,
            self::TEST_FILE);

        $this->assertInstanceOf('Psr\Log\LoggerInterface',$_oLogger);

        $this->deleteFile(self::TEST_FILE);
    }

    public function testFile()
    {
        $_oLogger = $this->createLogger(
            \Dawen\Logger\AdvancedLogger::LEVEL_DEBUG,
            self::TEST_FILE);

        $_oLogger->debug('testing');

        $this->assertTrue(is_file(self::TEST_PATH.self::TEST_FILE));
        $this->deleteFile(self::TEST_FILE);
    }

    public function testLoggerName()
    {
        $_sLogString = 'testing';

        $_oLogger = $this->createLogger(
            \Dawen\Logger\AdvancedLogger::LEVEL_DEBUG,
            self::TEST_FILE);

        $_oLogger->log(\Dawen\Logger\AdvancedLogger::LEVEL_DEBUG, $_sLogString);

        $_rHandle = fopen(self::TEST_PATH.self::TEST_FILE, "r");
        $_aContents = array();
        while (($_sBuffer = fgets($_rHandle, 4096)) !== false) {
            $_aContents[] = $_sBuffer;
        }
        fclose($_rHandle);

        $this->assertCount(1,$_aContents);
        $this->assertNotEmpty(strpos($_aContents[0],self::LOGGER_NAME.'.'));

        $this->deleteFile(self::TEST_FILE);
    }

    public function testLog()
    {
        $_sLogString = 'testing';
        $_iLevel = \Dawen\Logger\AdvancedLogger::LEVEL_DEBUG;
        $_sLevel = $this->aLevels[$_iLevel];

        $_oLogger = $this->createLogger(
            \Dawen\Logger\AdvancedLogger::LEVEL_DEBUG,
            self::TEST_FILE);

        $_bResult = $_oLogger->log(
                        $_iLevel,
                        $_sLogString,
                        $this->aContext,
                        $this->aExtra);

        $_rHandle = fopen(self::TEST_PATH.self::TEST_FILE, "r");
        $_aContents = array();
        while (($_sBuffer = fgets($_rHandle, 4096)) !== false) {
            $_aContents[] = $_sBuffer;
        }
        fclose($_rHandle);

        $this->assertTrue($_bResult);
        $this->assertCount(1,$_aContents);
        $this->assertContains('.'.$_sLevel, $_aContents[0]);
        $this->assertContains($_sLogString, $_aContents[0]);
        $this->assertContains('['.json_encode($this->aContext).']', $_aContents[0]);
        $this->assertContains('['.json_encode($this->aExtra).']', $_aContents[0]);

        $this->deleteFile(self::TEST_FILE);
    }

    public function testLevel()
    {
        $_sLogString = 'testing';
        $_iLevel = \Dawen\Logger\AdvancedLogger::LEVEL_ALERT;
        $_sLevel = $this->aLevels[$_iLevel];

        $_oLogger = $this->createLogger(
            \Dawen\Logger\AdvancedLogger::LEVEL_ALERT,
            self::TEST_FILE);

        $_bResult1 = $_oLogger->log(
            \Dawen\Logger\AdvancedLogger::LEVEL_ERROR,
            $_sLogString,
            $this->aContext,
            $this->aExtra);

        $_bResult2 = $_oLogger->log(
            \Dawen\Logger\AdvancedLogger::LEVEL_DEBUG,
            $_sLogString,
            $this->aContext,
            $this->aExtra);

        $_bResult = $_oLogger->log(
            $_iLevel,
            $_sLogString,
            $this->aContext,
            $this->aExtra);

        $_rHandle = fopen(self::TEST_PATH.self::TEST_FILE, "r");
        $_aContents = array();
        while (($_sBuffer = fgets($_rHandle, 4096)) !== false) {
            $_aContents[] = $_sBuffer;
        }
        fclose($_rHandle);

        $this->assertTrue($_bResult);
        $this->assertFalse($_bResult1);
        $this->assertFalse($_bResult2);
        $this->assertCount(1,$_aContents);
        $this->assertContains('.'.$_sLevel, $_aContents[0]);
        $this->assertContains($_sLogString, $_aContents[0]);
        $this->assertContains('['.json_encode($this->aContext).']', $_aContents[0]);
        $this->assertContains('['.json_encode($this->aExtra).']', $_aContents[0]);

        $this->deleteFile(self::TEST_FILE);
    }

    public function testGetLogger()
    {
        $_oLogger = $this->createLogger(
                Dawen\Logger\AdvancedLogger::LEVEL_DEBUG,
                self::TEST_FILE);

        $_oHandler = $_oLogger->getHandler();

        $this->assertInstanceOf('Dawen\Logger\Handler\HandlerInterface', $_oHandler);
        //$this->assertInstanceOf('Dawen\Logger\Hndler\Interface', $_oHandler);
    }


    public function testSetLogger()
    {
        $_oLogger = $this->createLogger(
            Dawen\Logger\AdvancedLogger::LEVEL_DEBUG,
            self::TEST_FILE);


        $_oHandler = new \Dawen\Logger\Handler\StreamFormatterHandler(
                    Dawen\Logger\AdvancedLogger::LEVEL_DEBUG,
                    self::TEST_FILE);

        $_oLogger->setHandler($_oHandler);
        $_oHandlerInstance = $_oLogger->getHandler();

        $this->assertInstanceOf('Dawen\Logger\Handler\HandlerInterface', $_oHandlerInstance);
        $this->assertInstanceOf('Dawen\Logger\Handler\HandlerFormatterInterface', $_oHandlerInstance);
    }

    public function testAlert()
    {
        $_sLogString = 'testing';
        $_iLevel = \Dawen\Logger\AdvancedLogger::LEVEL_ALERT;
        $_sLevel = $this->aLevels[$_iLevel];

        $_oLogger = $this->createLogger(
            \Dawen\Logger\AdvancedLogger::LEVEL_DEBUG,
            self::TEST_FILE);

        $_bResult = $_oLogger->alert(
                        $_sLogString,
                        $this->aContext,
                        $this->aExtra);

        $_rHandle = fopen(self::TEST_PATH.self::TEST_FILE, "r");
        $_aContents = array();
        while (($_sBuffer = fgets($_rHandle, 4096)) !== false) {
            $_aContents[] = $_sBuffer;
        }
        fclose($_rHandle);

        $this->assertTrue($_bResult);
        $this->assertCount(1,$_aContents);
        $this->assertContains('.'.$_sLevel, $_aContents[0]);
        $this->assertContains($_sLogString, $_aContents[0]);
        $this->assertContains('['.json_encode($this->aContext).']', $_aContents[0]);
        $this->assertContains('['.json_encode($this->aExtra).']', $_aContents[0]);

        $this->deleteFile(self::TEST_FILE);
    }

    public function testCritical()
    {
        $_sLogString = 'testing';
        $_iLevel = \Dawen\Logger\AdvancedLogger::LEVEL_CRITICAL;
        $_sLevel = $this->aLevels[$_iLevel];

        $_oLogger = $this->createLogger(
            \Dawen\Logger\AdvancedLogger::LEVEL_DEBUG,
            self::TEST_FILE);

        $_bResult = $_oLogger->critical(
            $_sLogString,
            $this->aContext,
            $this->aExtra);

        $_rHandle = fopen(self::TEST_PATH.self::TEST_FILE, "r");
        $_aContents = array();
        while (($_sBuffer = fgets($_rHandle, 4096)) !== false) {
            $_aContents[] = $_sBuffer;
        }
        fclose($_rHandle);

        $this->assertTrue($_bResult);
        $this->assertCount(1,$_aContents);
        $this->assertContains('.'.$_sLevel, $_aContents[0]);
        $this->assertContains($_sLogString, $_aContents[0]);
        $this->assertContains('['.json_encode($this->aContext).']', $_aContents[0]);
        $this->assertContains('['.json_encode($this->aExtra).']', $_aContents[0]);

        $this->deleteFile(self::TEST_FILE);
    }

    public function testDebug()
    {
        $_sLogString = 'testing';
        $_iLevel = \Dawen\Logger\AdvancedLogger::LEVEL_DEBUG;
        $_sLevel = $this->aLevels[$_iLevel];

        $_oLogger = $this->createLogger(
            \Dawen\Logger\AdvancedLogger::LEVEL_DEBUG,
            self::TEST_FILE);

        $_bResult = $_oLogger->debug(
            $_sLogString,
            $this->aContext,
            $this->aExtra);

        $_rHandle = fopen(self::TEST_PATH.self::TEST_FILE, "r");
        $_aContents = array();
        while (($_sBuffer = fgets($_rHandle, 4096)) !== false) {
            $_aContents[] = $_sBuffer;
        }
        fclose($_rHandle);

        $this->assertTrue($_bResult);
        $this->assertCount(1,$_aContents);
        $this->assertContains('.'.$_sLevel, $_aContents[0]);
        $this->assertContains($_sLogString, $_aContents[0]);
        $this->assertContains('['.json_encode($this->aContext).']', $_aContents[0]);
        $this->assertContains('['.json_encode($this->aExtra).']', $_aContents[0]);

        $this->deleteFile(self::TEST_FILE);
    }

    public function testError()
    {
        $_sLogString = 'testing';
        $_iLevel = \Dawen\Logger\AdvancedLogger::LEVEL_ERROR;
        $_sLevel = $this->aLevels[$_iLevel];

        $_oLogger = $this->createLogger(
            \Dawen\Logger\AdvancedLogger::LEVEL_DEBUG,
            self::TEST_FILE);

        $_bResult = $_oLogger->error(
            $_sLogString,
            $this->aContext,
            $this->aExtra);

        $_rHandle = fopen(self::TEST_PATH.self::TEST_FILE, "r");
        $_aContents = array();
        while (($_sBuffer = fgets($_rHandle, 4096)) !== false) {
            $_aContents[] = $_sBuffer;
        }
        fclose($_rHandle);

        $this->assertTrue($_bResult);
        $this->assertCount(1,$_aContents);
        $this->assertContains('.'.$_sLevel, $_aContents[0]);
        $this->assertContains($_sLogString, $_aContents[0]);
        $this->assertContains('['.json_encode($this->aContext).']', $_aContents[0]);
        $this->assertContains('['.json_encode($this->aExtra).']', $_aContents[0]);

        $this->deleteFile(self::TEST_FILE);
    }

    public function testInfo()
    {
        $_sLogString = 'testing';
        $_iLevel = \Dawen\Logger\AdvancedLogger::LEVEL_INFO;
        $_sLevel = $this->aLevels[$_iLevel];

        $_oLogger = $this->createLogger(
            \Dawen\Logger\AdvancedLogger::LEVEL_DEBUG,
            self::TEST_FILE);

        $_bResult = $_oLogger->info(
            $_sLogString,
            $this->aContext,
            $this->aExtra);

        $_rHandle = fopen(self::TEST_PATH.self::TEST_FILE, "r");
        $_aContents = array();
        while (($_sBuffer = fgets($_rHandle, 4096)) !== false) {
            $_aContents[] = $_sBuffer;
        }
        fclose($_rHandle);

        $this->assertTrue($_bResult);
        $this->assertCount(1,$_aContents);
        $this->assertContains('.'.$_sLevel, $_aContents[0]);
        $this->assertContains($_sLogString, $_aContents[0]);
        $this->assertContains('['.json_encode($this->aContext).']', $_aContents[0]);
        $this->assertContains('['.json_encode($this->aExtra).']', $_aContents[0]);

        $this->deleteFile(self::TEST_FILE);
    }

    public function testNotice()
    {
        $_sLogString = 'testing';
        $_iLevel = \Dawen\Logger\AdvancedLogger::LEVEL_NOTICE;
        $_sLevel = $this->aLevels[$_iLevel];

        $_oLogger = $this->createLogger(
            \Dawen\Logger\AdvancedLogger::LEVEL_DEBUG,
            self::TEST_FILE);

        $_bResult = $_oLogger->notice(
            $_sLogString,
            $this->aContext,
            $this->aExtra);

        $_rHandle = fopen(self::TEST_PATH.self::TEST_FILE, "r");
        $_aContents = array();
        while (($_sBuffer = fgets($_rHandle, 4096)) !== false) {
            $_aContents[] = $_sBuffer;
        }
        fclose($_rHandle);

        $this->assertTrue($_bResult);
        $this->assertCount(1,$_aContents);
        $this->assertContains('.'.$_sLevel, $_aContents[0]);
        $this->assertContains($_sLogString, $_aContents[0]);
        $this->assertContains('['.json_encode($this->aContext).']', $_aContents[0]);
        $this->assertContains('['.json_encode($this->aExtra).']', $_aContents[0]);

        $this->deleteFile(self::TEST_FILE);
    }

    public function testWarning()
    {
        $_sLogString = 'testing';
        $_iLevel = \Dawen\Logger\AdvancedLogger::LEVEL_WARNING;
        $_sLevel = $this->aLevels[$_iLevel];

        $_oLogger = $this->createLogger(
            \Dawen\Logger\AdvancedLogger::LEVEL_DEBUG,
            self::TEST_FILE);

        $_bResult = $_oLogger->warning(
            $_sLogString,
            $this->aContext,
            $this->aExtra);

        $_rHandle = fopen(self::TEST_PATH.self::TEST_FILE, "r");
        $_aContents = array();
        while (($_sBuffer = fgets($_rHandle, 4096)) !== false) {
            $_aContents[] = $_sBuffer;
        }
        fclose($_rHandle);

        $this->assertTrue($_bResult);
        $this->assertCount(1,$_aContents);
        $this->assertContains('.'.$_sLevel, $_aContents[0]);
        $this->assertContains($_sLogString, $_aContents[0]);
        $this->assertContains('['.json_encode($this->aContext).']', $_aContents[0]);
        $this->assertContains('['.json_encode($this->aExtra).']', $_aContents[0]);

        $this->deleteFile(self::TEST_FILE);
    }

    public function testEmergency()
    {
        $_sLogString = 'testing';
        $_iLevel = \Dawen\Logger\AdvancedLogger::LEVEL_EMERGENCY;
        $_sLevel = $this->aLevels[$_iLevel];

        $_oLogger = $this->createLogger(
            \Dawen\Logger\AdvancedLogger::LEVEL_DEBUG,
            self::TEST_FILE);

        $_bResult = $_oLogger->emergency(
            $_sLogString,
            $this->aContext,
            $this->aExtra);

        $_rHandle = fopen(self::TEST_PATH.self::TEST_FILE, "r");
        $_aContents = array();
        while (($_sBuffer = fgets($_rHandle, 4096)) !== false) {
            $_aContents[] = $_sBuffer;
        }
        fclose($_rHandle);

        $this->assertTrue($_bResult);
        $this->assertCount(1, $_aContents);
        $this->assertContains('.'.$_sLevel, $_aContents[0]);
        $this->assertContains($_sLogString, $_aContents[0]);
        $this->assertContains('['.json_encode($this->aContext).']', $_aContents[0]);
        $this->assertContains('['.json_encode($this->aExtra).']', $_aContents[0]);

        $this->deleteFile(self::TEST_FILE);
    }

    public function testEmerg()
    {
        $_sLogString = 'testing';
        $_iLevel = \Dawen\Logger\AdvancedLogger::LEVEL_EMERGENCY;
        $_sLevel = $this->aLevels[$_iLevel];

        $_oLogger = $this->createLogger(
            \Dawen\Logger\AdvancedLogger::LEVEL_DEBUG,
            self::TEST_FILE);

        $_bResult = $_oLogger->emerg(
            $_sLogString,
            $this->aContext,
            $this->aExtra);

        $_rHandle = fopen(self::TEST_PATH.self::TEST_FILE, "r");
        $_aContents = array();
        while (($_sBuffer = fgets($_rHandle, 4096)) !== false) {
            $_aContents[] = $_sBuffer;
        }
        fclose($_rHandle);

        $this->assertTrue($_bResult);
        $this->assertCount(1, $_aContents);
        $this->assertContains('.'.$_sLevel, $_aContents[0]);
        $this->assertContains($_sLogString, $_aContents[0]);
        $this->assertContains('['.json_encode($this->aContext).']', $_aContents[0]);
        $this->assertContains('['.json_encode($this->aExtra).']', $_aContents[0]);

        $this->deleteFile(self::TEST_FILE);
    }

}