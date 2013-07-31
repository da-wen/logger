<?php

class DebugHandlerTest extends \PHPUnit_Framework_TestCase
{
    const TEST_PATH = '/tmp';

    const LEVEL_DEBUG = 100;
    const LEVEL_ERROR = 400;

    /** @var null|\Dawen\Logger\Handler\DebugHandler */
    private $oHandler = null;

    private function createHandler($iLevel = self::LEVEL_DEBUG)
    {
        return new \Dawen\Logger\Handler\DebugHandler($iLevel);
    }

    protected function setUp()
    {
        parent::setUp();
        $this->oHandler = $this->createHandler(self::LEVEL_ERROR);
    }

    public function testInstance()
    {
        $this->assertInstanceOf('Dawen\Logger\Handler\HandlerInterface',$this->oHandler);
        $this->assertInstanceOf('Dawen\Logger\Handler\DebugHandlerInterface',$this->oHandler);
    }

    public function testWrongHandleParam()
    {
        $_iData = 100;
        try
        {
            $_bHandle = $this->oHandler->handle($_iData);
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
            $_aData = array('iLevelss' => self::LEVEL_DEBUG);
            $_bHandle = $this->oHandler->handle($_aData);
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
        $_bHandle = $this->oHandler->handle($_aData);
        $this->assertFalse($_bHandle);
    }

    public function testHandle()
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
            ),
            'aExtra' => array(
                'exrta1' => 'extra1',
                'extra2' => 2
            ),
        );
        $_bHandled = $this->oHandler->handle($_aData);
        $this->assertTrue($_bHandled);
    }

    public function testGetData()
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
            ),
            'aExtra' => array(
                'exrta1' => 'extra1',
                'extra2' => 2
            ),
        );
        $_bHandled = $this->oHandler->handle($_aData);
        $this->assertTrue($_bHandled);

        $_DebugData = $this->oHandler->getData();
        $this->assertEquals($_aData, $_DebugData[0]);

    }

    public function testSetMultipleData()
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
            ),
            'aExtra' => array(
                'exrta1' => 'extra1',
                'extra2' => 2
            ),
        );
        $_bHandled = $this->oHandler->handle($_aData);
        $_bHandled = $this->oHandler->handle($_aData);
        $_bHandled = $this->oHandler->handle($_aData);
        $_bHandled = $this->oHandler->handle($_aData);
        $_bHandled = $this->oHandler->handle($_aData);
        $this->assertTrue($_bHandled);

        $_DebugData = $this->oHandler->getData();
        $this->assertCount(5, $_DebugData);

    }
}