<?php
namespace Dawen\Logger\Handler;

class DebugHandler implements DebugHandlerInterface
{

    /**
     * datetime format for log entry
     */
    const DATETIME_FORMAT = 'Y-m-d H:i:s';

    /**
     * level which is written
     *
     * @var int
     */
    private $iLogLevel;

    /**
     * date format for log entry
     *
     * @var string
     */
    private $sDateTimeFormat = null;

    /**
     * data collector
     *
     * @var array
     */
    private $aData = array();

    public function __construct($iLogLevel, $sDateTimeFormat = null)
    {
        $this->iLogLevel  = $iLogLevel;
        $this->sDateTimeFormat =  (null !== $sDateTimeFormat) ? $sDateTimeFormat : self::DATETIME_FORMAT;
    }

    /**
     * get collected data
     *
     * @return array
     */
    public function getData()
    {
        return $this->aData;
    }

    /**
     * push log entry to array
     *
     * @param array $aEntry
     * @return bool
     */
    public function handle(array $aEntry)
    {

        if($this->iLogLevel > $aEntry['iLevel'])
        {
            return false;
        }

        $this->aData[] = $aEntry;
        return true;

    }

}