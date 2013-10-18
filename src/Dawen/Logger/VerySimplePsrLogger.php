<?php
namespace Dawen\Logger;

use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;

class VerySimplePsrLogger extends AbstractLogger
{
    /**
     * datetime format for log entry
     */
    const DATETIME_FORMAT = 'Y-m-d H:i:s';

    /**
     * log levels
     *
     * @var array
     */
    private $aLevels = array(
        LogLevel::DEBUG     => 100,
        LogLevel::INFO      => 200,
        LogLevel::NOTICE    => 250,
        LogLevel::WARNING   => 300,
        LogLevel::ERROR     => 400,
        LogLevel::CRITICAL  => 500,
        LogLevel::ALERT     => 550,
        LogLevel::EMERGENCY => 600
    );


    /**
     * date format for log entry
     *
     * @var string
     */
    private $sDateTimeFormat = null;

    /**
     * Current log level for decide to
     *
     * @var int
     */
    private $iLogLevel = null;

    /**
     * name of the logger
     *
     * @var string
     */
    private $sName = null;

    /**
     * file path and name for your log file
     *
     * @var string
     */
    private $sFilePath = null;

    /**
     * resource for file stream
     *
     * @var resource
     */
    private $rStream = null;


    /**
     * constructor of logger
     *
     * @param string $sName
     * @param int $iLogLevel
     * @param string $sFilePath
     * @param null|string $sDateTimeFormat
     */
    public function __construct($sName,$iLogLevel,$sFilePath, $sDateTimeFormat = null)
    {
        $this->sName = $sName;
        $this->iLogLevel = $iLogLevel;
        $this->sFilePath = $sFilePath;
        $this->sDateTimeFormat =  (null !== $sDateTimeFormat) ? $sDateTimeFormat : self::DATETIME_FORMAT;
    }

    /**
     * @inheritdoc
     */
    public function log($level, $message, array $context = array())
    {
        return $this->write($level,$message,$context);
    }

    /**
     * formats the log entry and returns the line.
     *
     * @param string $sLevel
     * @param string $sMessage
     * @param array $aContext
     * @return string
     */
    private function format($sLevel, $sMessage, array $aContext)
    {
        $_oDate = new \DateTime();

        $_sLine = '['.$_oDate->format($this->sDateTimeFormat).'] '.$this->sName.'.'.$sLevel.': ';
        $_sLine .= $sMessage;
        if(!empty($aContext))
        {
            $_sLine .= ' [context: '.json_encode($aContext).']';
        }
        $_sLine .= PHP_EOL;

        return $_sLine;
    }

    /**
     * writes message to file (appending)
     *
     * @param string $sLevel
     * @param string $sMessage
     * @param array $aContext
     * @return bool
     * @throws \UnexpectedValueException
     */
    private function write($sLevel, $sMessage, array $aContext = array())
    {
        if($this->iLogLevel > $this->aLevels[$sLevel])
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

        fwrite($this->rStream, (string)$this->format(
            $sLevel,
            $sMessage,
            $aContext));

        return true;
    }
}