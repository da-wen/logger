<?php
/**
 * Created by JetBrains PhpStorm.
 * User: dawen
 * Date: 20.09.13
 * Time: 19:02
 * To change this template use File | Settings | File Templates.
 */

class ProcessIdProcessor extends \PHPUnit_Framework_TestCase
{

    /** @var  Dawen\Logger\Processor\ProcessorInterface */
    private $oProcessIdProcessor;

    protected function setUp()
    {
        parent::setUp();
        $this->oProcessIdProcessor = new \Dawen\Logger\Processor\ProcessIdProcessor();
    }

    public function testInstance()
    {
        $this->assertInstanceOf('Dawen\Logger\Processor\ProcessorInterface', $this->oProcessIdProcessor);
    }

    public function testFormat()
    {
        $_aResult = $this->oProcessIdProcessor->execute(array());

        $this->assertTrue(isset($_aResult['aExtra']));
        $this->assertTrue(isset($_aResult['aExtra']['iProcessId']));
    }

    public function testValue()
    {
        $_aResult = $this->oProcessIdProcessor->execute(array());
        $_iProcessId = $_aResult['aExtra']['iProcessId'];

        $this->assertTrue(is_int($_iProcessId));
        $this->assertTrue($_iProcessId > 0);
    }
}