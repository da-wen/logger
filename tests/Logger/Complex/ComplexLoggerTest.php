<?php
/**
 * Created by JetBrains PhpStorm.
 * User: da-wen
 * Date: 16.05.13
 * Time: 08:17
 * To change this template use File | Settings | File Templates.
 */

class ComplexLoggerTest extends \PHPUnit_Framework_TestCase
{
    const TEST_PATH = '/tmp';
    const TEST_FILE_1 = '/DawenAdvancedLoggerUnitTest1.log';
    const TEST_FILE_2 = '/DawenAdvancedLoggerUnitTest2.log';
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

    private function createLogger($iLogLevel, $sTimeStampFormat = null)
    {
        if(!is_writable(self::TEST_PATH))
        {
            $this->markTestSkipped('/tmp is not writable');
        }

        $_sFilePath1 = self::TEST_PATH.self::TEST_FILE_1;
        $_sFilePath2 = self::TEST_PATH.self::TEST_FILE_2;

        $_oHandler1 = new \Dawen\Logger\Handler\StreamHandler($iLogLevel,$_sFilePath1);
        $_oHandler2 = new \Dawen\Logger\Handler\StreamFormatterHandler($iLogLevel,$_sFilePath2);
        $_oLogger = new \Dawen\Logger\ComplexLogger(self::LOGGER_NAME, $sTimeStampFormat);
        $_oLogger->addHandler($_oHandler1);
        $_oLogger->addHandler($_oHandler2);

        return $_oLogger;
    }

    private function deleteFile()
    {
        $_sFilePath1 = self::TEST_PATH.self::TEST_FILE_1;
        $_sFilePath2 = self::TEST_PATH.self::TEST_FILE_2;
        if(is_file($_sFilePath1))
        {
            unlink($_sFilePath1);
        }
        if(is_file($_sFilePath2))
        {
            unlink($_sFilePath2);
        }
    }

    public function testInstance()
    {
        $_oLogger = $this->createLogger(
            \Dawen\Logger\ComplexLogger::LEVEL_DEBUG);

        $this->assertInstanceOf('Psr\Log\LoggerInterface',$_oLogger);

        $this->deleteFile();
    }

    public function testNoHandler()
    {
        $_sLogString = 'testing';
        $_iLevel = \Dawen\Logger\AdvancedLogger::LEVEL_DEBUG;

        $_oLogger = new \Dawen\Logger\ComplexLogger(self::LOGGER_NAME);

        try
        {
            $_bResult = $_oLogger->log(
                $_iLevel,
                $_sLogString,
                $this->aContext,
                $this->aExtra);
        }
        catch(\Exception $_oException)
        {
            return;
        }

        $this->fail('An expected Exeption has not been raised.');
        $this->deleteFile();
    }

    public function testFile()
    {
        $_oLogger = $this->createLogger(
            \Dawen\Logger\AdvancedLogger::LEVEL_DEBUG);

        $_oLogger->debug('testing');

        $this->assertTrue(is_file(self::TEST_PATH.self::TEST_FILE_1));
        $this->assertTrue(is_file(self::TEST_PATH.self::TEST_FILE_2));
        $this->deleteFile();
    }

    public function testHandler()
    {
        $_oLogger = $this->createLogger(
            \Dawen\Logger\AdvancedLogger::LEVEL_DEBUG);

        $_aHandler = $_oLogger->getHandler();

        $this->assertCount(2, $_aHandler);
        foreach($_aHandler as $_oHandler)
        {
            $this->assertInstanceOf('Dawen\Logger\Handler\HandlerInterface', $_oHandler);
        }

        $this->deleteFile();
    }


    public function testLoggerName()
    {
        $_sLogString = 'testing';

        $_oLogger = $this->createLogger(
            \Dawen\Logger\AdvancedLogger::LEVEL_DEBUG);

        $_oLogger->log(\Dawen\Logger\AdvancedLogger::LEVEL_DEBUG, $_sLogString);

        // file 1
        $_rHandle = fopen(self::TEST_PATH.self::TEST_FILE_1, "r");
        $_aContents1 = array();
        while (($_sBuffer = fgets($_rHandle, 4096)) !== false) {
            $_aContents1[] = $_sBuffer;
        }
        fclose($_rHandle);

        // file 2
        $_rHandle = fopen(self::TEST_PATH.self::TEST_FILE_2, "r");
        $_aContents2 = array();
        while (($_sBuffer = fgets($_rHandle, 4096)) !== false) {
            $_aContents2[] = $_sBuffer;
        }
        fclose($_rHandle);

        $this->assertCount(1,$_aContents1);
        $this->assertContains(self::LOGGER_NAME.'.', $_aContents1[0]);

        $this->assertCount(1,$_aContents2);
        $this->assertContains(self::LOGGER_NAME.'.', $_aContents2[0]);

        $this->deleteFile();
    }

    public function testLog()
    {
        $_sLogString = 'testing';
        $_iLevel = \Dawen\Logger\AdvancedLogger::LEVEL_DEBUG;
        $_sLevel = $this->aLevels[$_iLevel];

        $_oLogger = $this->createLogger(
            \Dawen\Logger\AdvancedLogger::LEVEL_DEBUG);

        $_bResult = $_oLogger->log(
            $_iLevel,
            $_sLogString,
            $this->aContext,
            $this->aExtra);

        // file 1
        $_rHandle = fopen(self::TEST_PATH.self::TEST_FILE_1, "r");
        $_aContents1 = array();
        while (($_sBuffer = fgets($_rHandle, 4096)) !== false) {
            $_aContents1[] = $_sBuffer;
        }
        fclose($_rHandle);

        // file 2
        $_rHandle = fopen(self::TEST_PATH.self::TEST_FILE_2, "r");
        $_aContents2 = array();
        while (($_sBuffer = fgets($_rHandle, 4096)) !== false) {
            $_aContents2[] = $_sBuffer;
        }
        fclose($_rHandle);

        $this->assertTrue($_bResult);

        $this->assertCount(1,$_aContents1);
        $this->assertContains('.'.$_sLevel, $_aContents1[0]);
        $this->assertContains($_sLogString, $_aContents1[0]);
        $this->assertContains('['.json_encode($this->aContext).']', $_aContents1[0]);
        $this->assertContains('['.json_encode($this->aExtra).']', $_aContents1[0]);

        $this->assertCount(1,$_aContents2);
        $this->assertContains('.'.$_sLevel, $_aContents2[0]);
        $this->assertContains($_sLogString, $_aContents2[0]);
        $this->assertContains('['.json_encode($this->aContext).']', $_aContents2[0]);
        $this->assertContains('['.json_encode($this->aExtra).']', $_aContents2[0]);

        $this->deleteFile();
    }


    public function testLevel()
    {
        $_sLogString = 'testing';
        $_iLevel = \Dawen\Logger\AdvancedLogger::LEVEL_ALERT;
        $_sLevel = $this->aLevels[$_iLevel];

        $_oLogger = $this->createLogger(
            \Dawen\Logger\AdvancedLogger::LEVEL_ALERT);

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


        // file 1
        $_rHandle = fopen(self::TEST_PATH.self::TEST_FILE_1, "r");
        $_aContents1 = array();
        while (($_sBuffer = fgets($_rHandle, 4096)) !== false) {
            $_aContents1[] = $_sBuffer;
        }
        fclose($_rHandle);

        // file 2
        $_rHandle = fopen(self::TEST_PATH.self::TEST_FILE_2, "r");
        $_aContents2 = array();
        while (($_sBuffer = fgets($_rHandle, 4096)) !== false) {
            $_aContents2[] = $_sBuffer;
        }
        fclose($_rHandle);

        $this->assertTrue($_bResult);
        $this->assertTrue($_bResult1);
        $this->assertTrue($_bResult2);

        $this->assertCount(1,$_aContents1);
        $this->assertContains('.'.$_sLevel, $_aContents1[0]);
        $this->assertContains($_sLogString, $_aContents1[0]);
        $this->assertContains('['.json_encode($this->aContext).']', $_aContents1[0]);
        $this->assertContains('['.json_encode($this->aExtra).']', $_aContents1[0]);

        $this->assertCount(1,$_aContents2);
        $this->assertContains('.'.$_sLevel, $_aContents2[0]);
        $this->assertContains($_sLogString, $_aContents2[0]);
        $this->assertContains('['.json_encode($this->aContext).']', $_aContents2[0]);
        $this->assertContains('['.json_encode($this->aExtra).']', $_aContents2[0]);

        $this->deleteFile();
    }

    public function testAlert()
    {
        $_sLogString = 'testing';
        $_iLevel = \Dawen\Logger\AdvancedLogger::LEVEL_ALERT;
        $_sLevel = $this->aLevels[$_iLevel];

        $_oLogger = $this->createLogger(
            \Dawen\Logger\AdvancedLogger::LEVEL_DEBUG);

        $_bResult = $_oLogger->alert(
            $_sLogString,
            $this->aContext,
            $this->aExtra);

        // file 1
        $_rHandle = fopen(self::TEST_PATH.self::TEST_FILE_1, "r");
        $_aContents1 = array();
        while (($_sBuffer = fgets($_rHandle, 4096)) !== false) {
            $_aContents1[] = $_sBuffer;
        }
        fclose($_rHandle);

        // file 2
        $_rHandle = fopen(self::TEST_PATH.self::TEST_FILE_2, "r");
        $_aContents2 = array();
        while (($_sBuffer = fgets($_rHandle, 4096)) !== false) {
            $_aContents2[] = $_sBuffer;
        }
        fclose($_rHandle);

        $this->assertTrue($_bResult);

        $this->assertCount(1,$_aContents1);
        $this->assertContains('.'.$_sLevel, $_aContents1[0]);
        $this->assertContains($_sLogString, $_aContents1[0]);
        $this->assertContains('['.json_encode($this->aContext).']', $_aContents1[0]);
        $this->assertContains('['.json_encode($this->aExtra).']', $_aContents1[0]);

        $this->assertCount(1,$_aContents2);
        $this->assertContains('.'.$_sLevel, $_aContents2[0]);
        $this->assertContains($_sLogString, $_aContents2[0]);
        $this->assertContains('['.json_encode($this->aContext).']', $_aContents2[0]);
        $this->assertContains('['.json_encode($this->aExtra).']', $_aContents2[0]);

        $this->deleteFile();
    }

    public function testCritical()
    {
        $_sLogString = 'testing';
        $_iLevel = \Dawen\Logger\AdvancedLogger::LEVEL_CRITICAL;
        $_sLevel = $this->aLevels[$_iLevel];

        $_oLogger = $this->createLogger(
            \Dawen\Logger\AdvancedLogger::LEVEL_DEBUG);

        $_bResult = $_oLogger->critical(
            $_sLogString,
            $this->aContext,
            $this->aExtra);

        // file 1
        $_rHandle = fopen(self::TEST_PATH.self::TEST_FILE_1, "r");
        $_aContents1 = array();
        while (($_sBuffer = fgets($_rHandle, 4096)) !== false) {
            $_aContents1[] = $_sBuffer;
        }
        fclose($_rHandle);

        // file 2
        $_rHandle = fopen(self::TEST_PATH.self::TEST_FILE_2, "r");
        $_aContents2 = array();
        while (($_sBuffer = fgets($_rHandle, 4096)) !== false) {
            $_aContents2[] = $_sBuffer;
        }
        fclose($_rHandle);

        $this->assertTrue($_bResult);

        $this->assertCount(1,$_aContents1);
        $this->assertContains('.'.$_sLevel, $_aContents1[0]);
        $this->assertContains($_sLogString, $_aContents1[0]);
        $this->assertContains('['.json_encode($this->aContext).']', $_aContents1[0]);
        $this->assertContains('['.json_encode($this->aExtra).']', $_aContents1[0]);

        $this->assertCount(1,$_aContents2);
        $this->assertContains('.'.$_sLevel, $_aContents2[0]);
        $this->assertContains($_sLogString, $_aContents2[0]);
        $this->assertContains('['.json_encode($this->aContext).']', $_aContents2[0]);
        $this->assertContains('['.json_encode($this->aExtra).']', $_aContents2[0]);

        $this->deleteFile();
    }

    public function testDebug()
    {
        $_sLogString = 'testing';
        $_iLevel = \Dawen\Logger\AdvancedLogger::LEVEL_DEBUG;
        $_sLevel = $this->aLevels[$_iLevel];

        $_oLogger = $this->createLogger(
            \Dawen\Logger\AdvancedLogger::LEVEL_DEBUG);

        $_bResult = $_oLogger->debug(
            $_sLogString,
            $this->aContext,
            $this->aExtra);

        // file 1
        $_rHandle = fopen(self::TEST_PATH.self::TEST_FILE_1, "r");
        $_aContents1 = array();
        while (($_sBuffer = fgets($_rHandle, 4096)) !== false) {
            $_aContents1[] = $_sBuffer;
        }
        fclose($_rHandle);

        // file 2
        $_rHandle = fopen(self::TEST_PATH.self::TEST_FILE_2, "r");
        $_aContents2 = array();
        while (($_sBuffer = fgets($_rHandle, 4096)) !== false) {
            $_aContents2[] = $_sBuffer;
        }
        fclose($_rHandle);

        $this->assertTrue($_bResult);

        $this->assertCount(1,$_aContents1);
        $this->assertContains('.'.$_sLevel, $_aContents1[0]);
        $this->assertContains($_sLogString, $_aContents1[0]);
        $this->assertContains('['.json_encode($this->aContext).']', $_aContents1[0]);
        $this->assertContains('['.json_encode($this->aExtra).']', $_aContents1[0]);

        $this->assertCount(1,$_aContents2);
        $this->assertContains('.'.$_sLevel, $_aContents2[0]);
        $this->assertContains($_sLogString, $_aContents2[0]);
        $this->assertContains('['.json_encode($this->aContext).']', $_aContents2[0]);
        $this->assertContains('['.json_encode($this->aExtra).']', $_aContents2[0]);

        $this->deleteFile();
    }

    public function testError()
    {
        $_sLogString = 'testing';
        $_iLevel = \Dawen\Logger\AdvancedLogger::LEVEL_ERROR;
        $_sLevel = $this->aLevels[$_iLevel];

        $_oLogger = $this->createLogger(
            \Dawen\Logger\AdvancedLogger::LEVEL_DEBUG);

        $_bResult = $_oLogger->error(
            $_sLogString,
            $this->aContext,
            $this->aExtra);

        // file 1
        $_rHandle = fopen(self::TEST_PATH.self::TEST_FILE_1, "r");
        $_aContents1 = array();
        while (($_sBuffer = fgets($_rHandle, 4096)) !== false) {
            $_aContents1[] = $_sBuffer;
        }
        fclose($_rHandle);

        // file 2
        $_rHandle = fopen(self::TEST_PATH.self::TEST_FILE_2, "r");
        $_aContents2 = array();
        while (($_sBuffer = fgets($_rHandle, 4096)) !== false) {
            $_aContents2[] = $_sBuffer;
        }
        fclose($_rHandle);

        $this->assertTrue($_bResult);

        $this->assertCount(1,$_aContents1);
        $this->assertContains('.'.$_sLevel, $_aContents1[0]);
        $this->assertContains($_sLogString, $_aContents1[0]);
        $this->assertContains('['.json_encode($this->aContext).']', $_aContents1[0]);
        $this->assertContains('['.json_encode($this->aExtra).']', $_aContents1[0]);

        $this->assertCount(1,$_aContents2);
        $this->assertContains('.'.$_sLevel, $_aContents2[0]);
        $this->assertContains($_sLogString, $_aContents2[0]);
        $this->assertContains('['.json_encode($this->aContext).']', $_aContents2[0]);
        $this->assertContains('['.json_encode($this->aExtra).']', $_aContents2[0]);

        $this->deleteFile();
    }

    public function testInfo()
    {
        $_sLogString = 'testing';
        $_iLevel = \Dawen\Logger\AdvancedLogger::LEVEL_INFO;
        $_sLevel = $this->aLevels[$_iLevel];

        $_oLogger = $this->createLogger(
            \Dawen\Logger\AdvancedLogger::LEVEL_DEBUG);

        $_bResult = $_oLogger->info(
            $_sLogString,
            $this->aContext,
            $this->aExtra);

        // file 1
        $_rHandle = fopen(self::TEST_PATH.self::TEST_FILE_1, "r");
        $_aContents1 = array();
        while (($_sBuffer = fgets($_rHandle, 4096)) !== false) {
            $_aContents1[] = $_sBuffer;
        }
        fclose($_rHandle);

        // file 2
        $_rHandle = fopen(self::TEST_PATH.self::TEST_FILE_2, "r");
        $_aContents2 = array();
        while (($_sBuffer = fgets($_rHandle, 4096)) !== false) {
            $_aContents2[] = $_sBuffer;
        }
        fclose($_rHandle);

        $this->assertTrue($_bResult);

        $this->assertCount(1,$_aContents1);
        $this->assertContains('.'.$_sLevel, $_aContents1[0]);
        $this->assertContains($_sLogString, $_aContents1[0]);
        $this->assertContains('['.json_encode($this->aContext).']', $_aContents1[0]);
        $this->assertContains('['.json_encode($this->aExtra).']', $_aContents1[0]);

        $this->assertCount(1,$_aContents2);
        $this->assertContains('.'.$_sLevel, $_aContents2[0]);
        $this->assertContains($_sLogString, $_aContents2[0]);
        $this->assertContains('['.json_encode($this->aContext).']', $_aContents2[0]);
        $this->assertContains('['.json_encode($this->aExtra).']', $_aContents2[0]);

        $this->deleteFile();
    }

    public function testNotice()
    {
        $_sLogString = 'testing';
        $_iLevel = \Dawen\Logger\AdvancedLogger::LEVEL_NOTICE;
        $_sLevel = $this->aLevels[$_iLevel];

        $_oLogger = $this->createLogger(
            \Dawen\Logger\AdvancedLogger::LEVEL_DEBUG);

        $_bResult = $_oLogger->notice(
            $_sLogString,
            $this->aContext,
            $this->aExtra);

        // file 1
        $_rHandle = fopen(self::TEST_PATH.self::TEST_FILE_1, "r");
        $_aContents1 = array();
        while (($_sBuffer = fgets($_rHandle, 4096)) !== false) {
            $_aContents1[] = $_sBuffer;
        }
        fclose($_rHandle);

        // file 2
        $_rHandle = fopen(self::TEST_PATH.self::TEST_FILE_2, "r");
        $_aContents2 = array();
        while (($_sBuffer = fgets($_rHandle, 4096)) !== false) {
            $_aContents2[] = $_sBuffer;
        }
        fclose($_rHandle);

        $this->assertTrue($_bResult);

        $this->assertCount(1,$_aContents1);
        $this->assertContains('.'.$_sLevel, $_aContents1[0]);
        $this->assertContains($_sLogString, $_aContents1[0]);
        $this->assertContains('['.json_encode($this->aContext).']', $_aContents1[0]);
        $this->assertContains('['.json_encode($this->aExtra).']', $_aContents1[0]);

        $this->assertCount(1,$_aContents2);
        $this->assertContains('.'.$_sLevel, $_aContents2[0]);
        $this->assertContains($_sLogString, $_aContents2[0]);
        $this->assertContains('['.json_encode($this->aContext).']', $_aContents2[0]);
        $this->assertContains('['.json_encode($this->aExtra).']', $_aContents2[0]);

        $this->deleteFile();
    }

    public function testWarning()
    {
        $_sLogString = 'testing';
        $_iLevel = \Dawen\Logger\AdvancedLogger::LEVEL_WARNING;
        $_sLevel = $this->aLevels[$_iLevel];

        $_oLogger = $this->createLogger(
            \Dawen\Logger\AdvancedLogger::LEVEL_DEBUG);

        $_bResult = $_oLogger->warning(
            $_sLogString,
            $this->aContext,
            $this->aExtra);

        // file 1
        $_rHandle = fopen(self::TEST_PATH.self::TEST_FILE_1, "r");
        $_aContents1 = array();
        while (($_sBuffer = fgets($_rHandle, 4096)) !== false) {
            $_aContents1[] = $_sBuffer;
        }
        fclose($_rHandle);

        // file 2
        $_rHandle = fopen(self::TEST_PATH.self::TEST_FILE_2, "r");
        $_aContents2 = array();
        while (($_sBuffer = fgets($_rHandle, 4096)) !== false) {
            $_aContents2[] = $_sBuffer;
        }
        fclose($_rHandle);

        $this->assertTrue($_bResult);

        $this->assertCount(1,$_aContents1);
        $this->assertContains('.'.$_sLevel, $_aContents1[0]);
        $this->assertContains($_sLogString, $_aContents1[0]);
        $this->assertContains('['.json_encode($this->aContext).']', $_aContents1[0]);
        $this->assertContains('['.json_encode($this->aExtra).']', $_aContents1[0]);

        $this->assertCount(1,$_aContents2);
        $this->assertContains('.'.$_sLevel, $_aContents2[0]);
        $this->assertContains($_sLogString, $_aContents2[0]);
        $this->assertContains('['.json_encode($this->aContext).']', $_aContents2[0]);
        $this->assertContains('['.json_encode($this->aExtra).']', $_aContents2[0]);

        $this->deleteFile();
    }

    public function testEmergency()
    {
        $_sLogString = 'testing';
        $_iLevel = \Dawen\Logger\AdvancedLogger::LEVEL_EMERGENCY;
        $_sLevel = $this->aLevels[$_iLevel];

        $_oLogger = $this->createLogger(
            \Dawen\Logger\AdvancedLogger::LEVEL_DEBUG);

        $_bResult = $_oLogger->emergency(
            $_sLogString,
            $this->aContext,
            $this->aExtra);

        // file 1
        $_rHandle = fopen(self::TEST_PATH.self::TEST_FILE_1, "r");
        $_aContents1 = array();
        while (($_sBuffer = fgets($_rHandle, 4096)) !== false) {
            $_aContents1[] = $_sBuffer;
        }
        fclose($_rHandle);

        // file 2
        $_rHandle = fopen(self::TEST_PATH.self::TEST_FILE_2, "r");
        $_aContents2 = array();
        while (($_sBuffer = fgets($_rHandle, 4096)) !== false) {
            $_aContents2[] = $_sBuffer;
        }
        fclose($_rHandle);

        $this->assertTrue($_bResult);

        $this->assertCount(1,$_aContents1);
        $this->assertContains('.'.$_sLevel, $_aContents1[0]);
        $this->assertContains($_sLogString, $_aContents1[0]);
        $this->assertContains('['.json_encode($this->aContext).']', $_aContents1[0]);
        $this->assertContains('['.json_encode($this->aExtra).']', $_aContents1[0]);

        $this->assertCount(1,$_aContents2);
        $this->assertContains('.'.$_sLevel, $_aContents2[0]);
        $this->assertContains($_sLogString, $_aContents2[0]);
        $this->assertContains('['.json_encode($this->aContext).']', $_aContents2[0]);
        $this->assertContains('['.json_encode($this->aExtra).']', $_aContents2[0]);

        $this->deleteFile();
    }

    public function testEmerg()
    {
        $_sLogString = 'testing';
        $_iLevel = \Dawen\Logger\AdvancedLogger::LEVEL_EMERGENCY;
        $_sLevel = $this->aLevels[$_iLevel];

        $_oLogger = $this->createLogger(
            \Dawen\Logger\AdvancedLogger::LEVEL_DEBUG);

        $_bResult = $_oLogger->emerg(
            $_sLogString,
            $this->aContext,
            $this->aExtra);

        // file 1
        $_rHandle = fopen(self::TEST_PATH.self::TEST_FILE_1, "r");
        $_aContents1 = array();
        while (($_sBuffer = fgets($_rHandle, 4096)) !== false) {
            $_aContents1[] = $_sBuffer;
        }
        fclose($_rHandle);

        // file 2
        $_rHandle = fopen(self::TEST_PATH.self::TEST_FILE_2, "r");
        $_aContents2 = array();
        while (($_sBuffer = fgets($_rHandle, 4096)) !== false) {
            $_aContents2[] = $_sBuffer;
        }
        fclose($_rHandle);

        $this->assertTrue($_bResult);

        $this->assertCount(1,$_aContents1);
        $this->assertContains('.'.$_sLevel, $_aContents1[0]);
        $this->assertContains($_sLogString, $_aContents1[0]);
        $this->assertContains('['.json_encode($this->aContext).']', $_aContents1[0]);
        $this->assertContains('['.json_encode($this->aExtra).']', $_aContents1[0]);

        $this->assertCount(1,$_aContents2);
        $this->assertContains('.'.$_sLevel, $_aContents2[0]);
        $this->assertContains($_sLogString, $_aContents2[0]);
        $this->assertContains('['.json_encode($this->aContext).']', $_aContents2[0]);
        $this->assertContains('['.json_encode($this->aExtra).']', $_aContents2[0]);

        $this->deleteFile();
    }

    public function testDebugHandler()
    {
        $_oHandler = new \Dawen\Logger\Handler\DebugHandler(\Dawen\Logger\ComplexLogger::LEVEL_DEBUG);
        $_oLogger = new \Dawen\Logger\ComplexLogger(self::LOGGER_NAME);
        $_oLogger->setDebugHandler($_oHandler);

        $_sLogString = 'testing';

        $_bLogged = $_oLogger->debug($_sLogString);
        $this->assertTrue($_bLogged);

        $_aHandler = $_oLogger->getHandler();
        $this->assertTrue(array_key_exists(\Dawen\Logger\ComplexLogger::DEBUG_HANDLER_KEY, $_aHandler));

        $_oHandler = $_aHandler[\Dawen\Logger\ComplexLogger::DEBUG_HANDLER_KEY];
        $this->assertInstanceOf('Dawen\Logger\Handler\DebugHandlerInterface', $_oHandler);

        $_aData = $_oHandler->getData();
        $this->assertEquals(100, $_aData[0]['iLevel']);
        $this->assertEquals($_sLogString, $_aData[0]['sMessage']);
    }

    public function testProcessor()
    {
        $_sLogString = 'testing';
        $_iLevel = \Dawen\Logger\AdvancedLogger::LEVEL_DEBUG;
        $_sLevel = $this->aLevels[$_iLevel];

        $_oLogger = $this->createLogger(
            \Dawen\Logger\AdvancedLogger::LEVEL_DEBUG);

        $_oLogger->addProcessor(new \Dawen\Logger\Processor\ProcessIdProcessor());

        $_bResult = $_oLogger->debug(
            $_sLogString,
            $this->aContext,
            $this->aExtra);

        // file 1
        $_rHandle = fopen(self::TEST_PATH.self::TEST_FILE_1, "r");
        $_aContents1 = array();
        while (($_sBuffer = fgets($_rHandle, 4096)) !== false) {
            $_aContents1[] = $_sBuffer;
        }
        fclose($_rHandle);

        $this->assertTrue($_bResult);

        $this->assertCount(1,$_aContents1);
        $this->assertContains('iProcessId', $_aContents1[0]);
        $this->assertContains('iProcessId', $_aContents1[0]);

        $this->deleteFile();
    }

}