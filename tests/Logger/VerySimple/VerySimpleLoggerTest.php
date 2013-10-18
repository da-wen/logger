<?php
/**
 * Created by JetBrains PhpStorm.
 * User: da-wen
 * Date: 16.05.13
 * Time: 08:17
 * To change this template use File | Settings | File Templates.
 */

class VerySimpleLoggerTest extends \PHPUnit_Framework_TestCase
{
    const TEST_PATH = '/tmp';
    const TEST_FILE = '/DawenLoggerUnitTest.log';
    const LOGGER_NAME = 'UnitTestLogger';

    private $aLevels = array(
        \Dawen\Logger\VerySimpleLogger::LEVEL_DEBUG     => 'DEBUG',
        \Dawen\Logger\VerySimpleLogger::LEVEL_INFO      => 'INFO',
        \Dawen\Logger\VerySimpleLogger::LEVEL_NOTICE    => 'NOTICE',
        \Dawen\Logger\VerySimpleLogger::LEVEL_WARNING   => 'WARNING',
        \Dawen\Logger\VerySimpleLogger::LEVEL_ERROR     => 'ERROR',
        \Dawen\Logger\VerySimpleLogger::LEVEL_CRITICAL  => 'CRITICAL',
        \Dawen\Logger\VerySimpleLogger::LEVEL_ALERT     => 'ALERT',
        \Dawen\Logger\VerySimpleLogger::LEVEL_EMERGENCY => 'EMERGENCY',
    );

    private function createLogger($iLogLevel,$sFileName, $sTimeStampFormat = null)
    {
        if(!is_writable(self::TEST_PATH))
        {
            $this->markTestSkipped('/tmp is not writable');
        }

        $_sFilePath = self::TEST_PATH.$sFileName;

        return new \Dawen\Logger\VerySimpleLogger(
                self::LOGGER_NAME,
                $iLogLevel,
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

    public function testInstance()
    {
        $_oLogger = $this->createLogger(
            \Dawen\Logger\VerySimpleLogger::LEVEL_DEBUG,
            self::TEST_FILE);

        $this->assertInstanceOf('Psr\Log\LoggerInterface',$_oLogger);

        $this->deleteFile(self::TEST_FILE);
    }

    public function testFile()
    {
        $_oLogger = $this->createLogger(
            \Dawen\Logger\VerySimpleLogger::LEVEL_DEBUG,
            self::TEST_FILE);

        $_oLogger->debug('testing');

        $this->assertTrue(is_file(self::TEST_PATH.self::TEST_FILE));
        $this->deleteFile(self::TEST_FILE);
    }

    public function testLoggerName()
    {
        $_sLogString = 'testing';

        $_oLogger = $this->createLogger(
            \Dawen\Logger\VerySimpleLogger::LEVEL_DEBUG,
            self::TEST_FILE);

        $_oLogger->log(\Dawen\Logger\VerySimpleLogger::LEVEL_DEBUG, $_sLogString);

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

        $_oLogger = $this->createLogger(
            \Dawen\Logger\VerySimpleLogger::LEVEL_DEBUG,
            self::TEST_FILE);

        $_oLogger->log(\Dawen\Logger\VerySimpleLogger::LEVEL_DEBUG, $_sLogString);

        $_rHandle = fopen(self::TEST_PATH.self::TEST_FILE, "r");
        $_aContents = array();
        while (($_sBuffer = fgets($_rHandle, 4096)) !== false) {
            $_aContents[] = $_sBuffer;
        }
        fclose($_rHandle);

        $this->assertCount(1,$_aContents);
        $this->assertNotEmpty(strpos($_aContents[0],'.DEBUG'));
        $this->assertNotEmpty(strpos($_aContents[0],' '.$_sLogString));

        $this->deleteFile(self::TEST_FILE);
    }

    public function testDateTimeFormatDefault()
    {
        $_sLogString = 'testing';

        $_oLogger = $this->createLogger(
            \Dawen\Logger\VerySimpleLogger::LEVEL_DEBUG,
            self::TEST_FILE);

        $_oLogger->log(\Dawen\Logger\VerySimpleLogger::LEVEL_DEBUG, $_sLogString);

        $_rHandle = fopen(self::TEST_PATH.self::TEST_FILE, "r");
        $_aContents = array();
        while (($_sBuffer = fgets($_rHandle, 4096)) !== false) {
            $_aContents[] = $_sBuffer;
        }
        fclose($_rHandle);

        $this->assertCount(1,$_aContents);

        $_iMatches = preg_match('/^\[(.*)\] .*/i',$_aContents[0], $_aMatches);
        $this->assertEquals(1,$_iMatches);
        $this->assertEquals($_aMatches[1],date('Y-m-d H:i:s',strtotime($_aMatches[1])));
        $this->assertEquals(19,strlen($_aMatches[1]));

        $this->deleteFile(self::TEST_FILE);


    }

    public function testDateTimeFormat()
    {
        $_sLogString = 'testing';

        $_oLogger = $this->createLogger(
            \Dawen\Logger\VerySimpleLogger::LEVEL_DEBUG,
            self::TEST_FILE,
            'YmdHis');

        $_oLogger->log(\Dawen\Logger\VerySimpleLogger::LEVEL_DEBUG, $_sLogString);

        $_rHandle = fopen(self::TEST_PATH.self::TEST_FILE, "r");
        $_aContents = array();
        while (($_sBuffer = fgets($_rHandle, 4096)) !== false) {
            $_aContents[] = $_sBuffer;
        }
        fclose($_rHandle);

        $this->assertCount(1,$_aContents);

        $_iMatches = preg_match('/\[(.*)\] .*/i',$_aContents[0], $_aMatches);
        $this->assertTrue(is_int((int)$_aMatches[1]));
        $this->assertNotEmpty((int)$_aMatches[1]);
        $this->assertEquals(1,$_iMatches);
        $this->assertEquals($_aMatches[1],date('YmdHis',strtotime($_aMatches[1])));
        $this->assertEquals(14,strlen($_aMatches[1]));

        $this->deleteFile(self::TEST_FILE);
    }

    public function testMessage()
    {
        $_sLogString = 'testing';
        $_sLogString2 = 'testing2';

        $_oLogger = $this->createLogger(
            \Dawen\Logger\VerySimpleLogger::LEVEL_DEBUG,
            self::TEST_FILE,
            'YmdHis');

        $_oLogger->log(\Dawen\Logger\VerySimpleLogger::LEVEL_DEBUG, $_sLogString);
        $_oLogger->log(\Dawen\Logger\VerySimpleLogger::LEVEL_DEBUG, $_sLogString2);

        $_rHandle = fopen(self::TEST_PATH.self::TEST_FILE, "r");
        $_aContents = array();
        while (($_sBuffer = fgets($_rHandle, 4096)) !== false) {
            $_aContents[] = $_sBuffer;
        }
        fclose($_rHandle);

        $this->assertCount(2,$_aContents);
        $this->assertNotEmpty(strpos($_aContents[0],' '.$_sLogString.PHP_EOL));
        $this->assertNotEmpty(strpos($_aContents[1],' '.$_sLogString2.PHP_EOL));

        $this->deleteFile(self::TEST_FILE);
    }

    public function testContext()
    {
        $_sLogString = 'testing';
        $_aContext = array('entry' => 'nothing', 'magic' => null);

        $_oLogger = $this->createLogger(
            \Dawen\Logger\VerySimpleLogger::LEVEL_DEBUG,
            self::TEST_FILE,
            'YmdHis');

        $_oLogger->log(\Dawen\Logger\VerySimpleLogger::LEVEL_DEBUG, $_sLogString,$_aContext);

        $_rHandle = fopen(self::TEST_PATH.self::TEST_FILE, "r");
        $_aContents = array();
        while (($_sBuffer = fgets($_rHandle, 4096)) !== false) {
            $_aContents[] = $_sBuffer;
        }
        fclose($_rHandle);

        $this->assertCount(1,$_aContents);

        $_iMatches = preg_match('/\[context: (.*)\]/i',$_aContents[0], $_aMatches);
        $this->assertEquals(1,$_iMatches);
        $this->assertEquals($_aContext,json_decode($_aMatches[1],true));

        $this->deleteFile(self::TEST_FILE);
    }

    public function testNullResource()
    {
        $_sLogString = 'testing';

        $_oLogger = $this->createLogger(
            \Dawen\Logger\VerySimpleLogger::LEVEL_DEBUG,
            null);

        $_oException = null;
        try
        {
            $_oLogger->log(\Dawen\Logger\VerySimpleLogger::LEVEL_DEBUG, $_sLogString);
        }
        catch(\Exception $_oException)
        {
            //do nothing
        }
        $this->assertInstanceOf('\UnexpectedValueException',$_oException);
    }

    public function testLogLevelEmergency()
    {
        $_sLogString = 'testing';
        $_iLogLevel = \Dawen\Logger\VerySimpleLogger::LEVEL_EMERGENCY;

        $_oLogger = $this->createLogger(
            $_iLogLevel,
            self::TEST_FILE);

        foreach($this->aLevels as $_iAddLevel => $_sAddLevelName)
        {
            $_oLogger->log($_iAddLevel, $_sLogString.' '.$_sAddLevelName);
        }

        $_rHandle = fopen(self::TEST_PATH.self::TEST_FILE, "r");
        $_aContents = array();
        while (($_sBuffer = fgets($_rHandle, 4096)) !== false) {
            $_aContents[] = $_sBuffer;
        }
        fclose($_rHandle);

        $this->assertCount(1,$_aContents);
        $this->assertNotEmpty(strpos($_aContents[0],'.'.$this->aLevels[$_iLogLevel]));

        $this->deleteFile(self::TEST_FILE);
    }

    public function testLogLevelError()
    {
        $_sLogString = 'testing';
        $_iLogLevel = \Dawen\Logger\VerySimpleLogger::LEVEL_ERROR;

        $_oLogger = $this->createLogger(
            $_iLogLevel,
            self::TEST_FILE);

        foreach($this->aLevels as $_iAddLevel => $_sAddLevelName)
        {
            $_oLogger->log($_iAddLevel, $_sLogString.' '.$_sAddLevelName);
        }

        $_rHandle = fopen(self::TEST_PATH.self::TEST_FILE, "r");
        $_aContents = array();
        while (($_sBuffer = fgets($_rHandle, 4096)) !== false) {
            $_aContents[] = $_sBuffer;
        }
        fclose($_rHandle);

        $this->assertCount(4,$_aContents);
        $this->assertNotEmpty(strpos($_aContents[0],'.'.$this->aLevels[$_iLogLevel]));

        $this->deleteFile(self::TEST_FILE);
    }

    public function testLogLevelInfo()
    {
        $_sLogString = 'testing';
        $_iLogLevel = \Dawen\Logger\VerySimpleLogger::LEVEL_INFO;

        $_oLogger = $this->createLogger(
            $_iLogLevel,
            self::TEST_FILE);

        foreach($this->aLevels as $_iAddLevel => $_sAddLevelName)
        {
            $_oLogger->log($_iAddLevel, $_sLogString.' '.$_sAddLevelName);
        }

        $_rHandle = fopen(self::TEST_PATH.self::TEST_FILE, "r");
        $_aContents = array();
        while (($_sBuffer = fgets($_rHandle, 4096)) !== false) {
            $_aContents[] = $_sBuffer;
        }
        fclose($_rHandle);

        $this->assertCount(7,$_aContents);
        $this->assertNotEmpty(strpos($_aContents[0],'.'.$this->aLevels[$_iLogLevel]));

        $this->deleteFile(self::TEST_FILE);
    }

    public function testDebug()
    {
        $_sLogString = 'testing';

        $_oLogger = $this->createLogger(
            \Dawen\Logger\VerySimpleLogger::LEVEL_DEBUG,
            self::TEST_FILE);

        $_oLogger->debug($_sLogString);

        $_rHandle = fopen(self::TEST_PATH.self::TEST_FILE, "r");
        $_aContents = array();
        while (($_sBuffer = fgets($_rHandle, 4096)) !== false) {
            $_aContents[] = $_sBuffer;
        }
        fclose($_rHandle);

        $this->assertCount(1,$_aContents);
        $this->assertNotEmpty(strpos($_aContents[0],'.DEBUG'));

        $this->deleteFile(self::TEST_FILE);
    }

    public function testInfo()
    {
        $_sLogString = 'testing';

        $_oLogger = $this->createLogger(
            \Dawen\Logger\VerySimpleLogger::LEVEL_DEBUG,
            self::TEST_FILE);

        $_oLogger->info($_sLogString);

        $_rHandle = fopen(self::TEST_PATH.self::TEST_FILE, "r");
        $_aContents = array();
        while (($_sBuffer = fgets($_rHandle, 4096)) !== false) {
            $_aContents[] = $_sBuffer;
        }
        fclose($_rHandle);

        $this->assertCount(1,$_aContents);
        $this->assertNotEmpty(strpos($_aContents[0],'.INFO'));

        $this->deleteFile(self::TEST_FILE);
    }

    public function testNotice()
    {
        $_sLogString = 'testing';

        $_oLogger = $this->createLogger(
            \Dawen\Logger\VerySimpleLogger::LEVEL_DEBUG,
            self::TEST_FILE);

        $_oLogger->Notice($_sLogString);

        $_rHandle = fopen(self::TEST_PATH.self::TEST_FILE, "r");
        $_aContents = array();
        while (($_sBuffer = fgets($_rHandle, 4096)) !== false) {
            $_aContents[] = $_sBuffer;
        }
        fclose($_rHandle);

        $this->assertCount(1,$_aContents);
        $this->assertNotEmpty(strpos($_aContents[0],'.NOTICE'));

        $this->deleteFile(self::TEST_FILE);
    }

    public function testWarning()
    {
        $_sLogString = 'testing';

        $_oLogger = $this->createLogger(
            \Dawen\Logger\VerySimpleLogger::LEVEL_DEBUG,
            self::TEST_FILE);

        $_oLogger->warning($_sLogString);

        $_rHandle = fopen(self::TEST_PATH.self::TEST_FILE, "r");
        $_aContents = array();
        while (($_sBuffer = fgets($_rHandle, 4096)) !== false) {
            $_aContents[] = $_sBuffer;
        }
        fclose($_rHandle);

        $this->assertCount(1,$_aContents);
        $this->assertNotEmpty(strpos($_aContents[0],'.WARNING'));

        $this->deleteFile(self::TEST_FILE);
    }

    public function testError()
    {
        $_sLogString = 'testing';

        $_oLogger = $this->createLogger(
            \Dawen\Logger\VerySimpleLogger::LEVEL_DEBUG,
            self::TEST_FILE);

        $_oLogger->error($_sLogString);

        $_rHandle = fopen(self::TEST_PATH.self::TEST_FILE, "r");
        $_aContents = array();
        while (($_sBuffer = fgets($_rHandle, 4096)) !== false) {
            $_aContents[] = $_sBuffer;
        }
        fclose($_rHandle);

        $this->assertCount(1,$_aContents);
        $this->assertNotEmpty(strpos($_aContents[0],'.ERROR'));

        $this->deleteFile(self::TEST_FILE);
    }

    public function testCritical()
    {
        $_sLogString = 'testing';

        $_oLogger = $this->createLogger(
            \Dawen\Logger\VerySimpleLogger::LEVEL_DEBUG,
            self::TEST_FILE);

        $_oLogger->critical($_sLogString);

        $_rHandle = fopen(self::TEST_PATH.self::TEST_FILE, "r");
        $_aContents = array();
        while (($_sBuffer = fgets($_rHandle, 4096)) !== false) {
            $_aContents[] = $_sBuffer;
        }
        fclose($_rHandle);

        $this->assertCount(1,$_aContents);
        $this->assertNotEmpty(strpos($_aContents[0],'.CRITICAL'));

        $this->deleteFile(self::TEST_FILE);
    }

    public function testAlert()
    {
        $_sLogString = 'testing';

        $_oLogger = $this->createLogger(
            \Dawen\Logger\VerySimpleLogger::LEVEL_DEBUG,
            self::TEST_FILE);

        $_oLogger->alert($_sLogString);

        $_rHandle = fopen(self::TEST_PATH.self::TEST_FILE, "r");
        $_aContents = array();
        while (($_sBuffer = fgets($_rHandle, 4096)) !== false) {
            $_aContents[] = $_sBuffer;
        }
        fclose($_rHandle);

        $this->assertCount(1,$_aContents);
        $this->assertNotEmpty(strpos($_aContents[0],'.ALERT'));

        $this->deleteFile(self::TEST_FILE);
    }

    public function testEmergency()
    {
        $_sLogString = 'testing';

        $_oLogger = $this->createLogger(
            \Dawen\Logger\VerySimpleLogger::LEVEL_DEBUG,
            self::TEST_FILE);

        $_oLogger->emergency($_sLogString);

        $_rHandle = fopen(self::TEST_PATH.self::TEST_FILE, "r");
        $_aContents = array();
        while (($_sBuffer = fgets($_rHandle, 4096)) !== false) {
            $_aContents[] = $_sBuffer;
        }
        fclose($_rHandle);

        $this->assertCount(1,$_aContents);
        $this->assertNotEmpty(strpos($_aContents[0],'.EMERGENCY'));

        $this->deleteFile(self::TEST_FILE);
    }

    public function testEmerg()
    {
        $_sLogString = 'testing';

        $_oLogger = $this->createLogger(
            \Dawen\Logger\VerySimpleLogger::LEVEL_DEBUG,
            self::TEST_FILE);

        $_oLogger->emerg($_sLogString);

        $_rHandle = fopen(self::TEST_PATH.self::TEST_FILE, "r");
        $_aContents = array();
        while (($_sBuffer = fgets($_rHandle, 4096)) !== false) {
            $_aContents[] = $_sBuffer;
        }
        fclose($_rHandle);

        $this->assertCount(1,$_aContents);
        $this->assertNotEmpty(strpos($_aContents[0],'.EMERGENCY'));

        $this->deleteFile(self::TEST_FILE);
    }

}