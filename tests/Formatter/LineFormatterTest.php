<?php
/**
 * Created by JetBrains PhpStorm.
 * User: da-wen
 * Date: 04.07.13
 * Time: 22:30
 * To change this template use File | Settings | File Templates.
 */
class LineFormatterTest extends \PHPUnit_Framework_TestCase
{

    /** @var null|\Dawen\Logger\Formatter\LineFormatter */
    private $oFormatter = null;

    protected function setUp()
    {
        parent::setUp();
        $this->oFormatter = new \Dawen\Logger\Formatter\LineFormatter();
    }

    public function testFormatFull()
    {
        $_oDateTime = new \DateTime();
        $_aData = array(
            'iLevel' => 500,
            'sLevel' => 'debug',
            'sTimestamp' => $_oDateTime->format('Y-m-d H:i:s'),
            'sLoggerName' => 'formattertest',
            'sMessage' => 'this is a debug message',
            'aContext' => array(
                'var1' => 'val1',
                'var2' => 2
            ),
            'aExtra' => array(
                'extra1' => 'val1',
                'extra2' => 2
            )
        );

        $_sFormatted = $this->oFormatter->format($_aData);
        $this->assertContains('['.$_oDateTime->format('Y-m-d H:i:s').']', $_sFormatted);
        $this->assertContains($_aData['sLoggerName'].'.'.$_aData['sLevel'], $_sFormatted);
        $this->assertContains($_aData['sMessage'], $_sFormatted);
        $this->assertContains('['.json_encode($_aData['aContext']).']',$_sFormatted);
        $this->assertContains('['.json_encode($_aData['aExtra']).']',$_sFormatted);
        $this->assertContains(PHP_EOL,$_sFormatted);
    }
}