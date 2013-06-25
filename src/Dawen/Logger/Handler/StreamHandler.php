<?php
namespace Dawen\Logger\Handler;

class StreamHandler implements HandlerInterface
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
     * file path and name for yout log file
     *
     * @var string
     */
    private $sFilePath = null;

    /**
     * dateformat for log entry
     *
     * @var string
     */
    private $sDateTimeFormat = null;

    /**
     * resource for file stream
     *
     * @var resource
     */
    private $rStream = null;

    public function __construct($iLogLevel, $sFilePath, $sDateTimeFormat = null)
    {
        $this->iLogLevel  = $iLogLevel;
        $this->sFilePath = $sFilePath;
        $this->sDateTimeFormat =  (null !== $sDateTimeFormat) ? $sDateTimeFormat : self::DATETIME_FORMAT;
    }

    public function handle(array $aEntry)
    {

        if($this->iLogLevel > $aEntry['iLevel'])
        {
            return false;
        }

        $_sErrorMessage = null;
        set_error_handler(function ($mCode, $sMsg) use (&$_sErrorMessage) {
            $_sErrorMessage = preg_replace('{^fopen\(.*?\): }', '', $sMsg);
        });
        $this->rStream = fopen($this->sFilePath, 'a');
        restore_error_handler();
        if (!is_resource($this->rStream)) {
            $this->rStream = null;
            throw new \UnexpectedValueException(sprintf('The stream or file "%s" could not be opened: '.$_sErrorMessage, $this->sFilePath));
        }

        $aEntry['formatted'] = $this->format($aEntry);
        fwrite($this->rStream, $aEntry['formatted']);

        return true;

    }

    /**
     * formats the log entry and returns the line.
     *
     * @param string $sLevel
     * @param string $sMessage
     * @param array $aContext
     * @return string
     */
    private function format(array $aEntry)
    {
        $_oDate = new \DateTime();

        $_sLine = '['.$_oDate->format($this->sDateTimeFormat).'] '.$aEntry['sLoggerName'].'.'.$aEntry['sLevel'].': ';
        $_sLine .= $aEntry['sMessage'];
        if(!empty($aEntry['aContext']))
        {
            $_sLine .= ' [context: '.json_encode($aEntry['aContext']).']';
        }
        $_sLine .= PHP_EOL;

        return $_sLine;
    }

}