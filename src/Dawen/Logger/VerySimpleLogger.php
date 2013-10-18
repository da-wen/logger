<?php
namespace Dawen\Logger;

use Psr\Log\LoggerInterface;

class VerySimpleLogger implements LoggerInterface
{
    /**
     * datetime format for log entry
     */
    const DATETIME_FORMAT = 'Y-m-d H:i:s';

    /**
     * Log level constants
     */
    const LEVEL_DEBUG     = 100;
    const LEVEL_INFO      = 200;
    const LEVEL_NOTICE    = 250;
    const LEVEL_WARNING   = 300;
    const LEVEL_ERROR     = 400;
    const LEVEL_CRITICAL  = 500;
    const LEVEL_ALERT     = 550;
    const LEVEL_EMERGENCY = 600;

    /**
     * log level names
     *
     * @var array
     */
    private static $aLevels = array(
        self::LEVEL_DEBUG     => 'DEBUG',
        self::LEVEL_INFO      => 'INFO',
        self::LEVEL_NOTICE    => 'NOTICE',
        self::LEVEL_WARNING   => 'WARNING',
        self::LEVEL_ERROR     => 'ERROR',
        self::LEVEL_CRITICAL  => 'CRITICAL',
        self::LEVEL_ALERT     => 'ALERT',
        self::LEVEL_EMERGENCY => 'EMERGENCY',
    );

    /**
     * date format for log entry
     *
     * @var string
     */
    private $sDateTimeFormat = null;

    /**
     * level which is written
     *
     * @var int
     */
    private $iLogLevel;

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
     * @inheritdoc
     */
    public function alert($message, array $context = array())
    {
        return $this->write(self::LEVEL_ALERT,$message,$context);
    }

    /**
     * @inheritdoc
     */
    public function critical($message, array $context = array())
    {
        return $this->write(self::LEVEL_CRITICAL,$message,$context);
    }

    /**
     * @inheritdoc
     */
    public function debug($message, array $context = array())
    {
        return $this->write(self::LEVEL_DEBUG,$message,$context);
    }

    /**
     * @inheritdoc
     */
    public function error($message, array $context = array())
    {
        return $this->write(self::LEVEL_ERROR,$message,$context);
    }

    /**
     * @inheritdoc
     */
    public function info($message, array $context = array())
    {
        return $this->write(self::LEVEL_INFO,$message,$context);
    }

    /**
     * @inheritdoc
     */
    public function notice($message, array $context = array())
    {
        return $this->write(self::LEVEL_NOTICE,$message,$context);
    }

    /**
     * @inheritdoc
     */
    public function warning($message, array $context = array())
    {
        return $this->write(self::LEVEL_WARNING,$message,$context);
    }

    /**
     * @inheritdoc
     */
    public function emerg($message, array $context = array())
    {
        return $this->write(self::LEVEL_EMERGENCY,$message,$context);
    }

    /**
     * @inheritdoc
     */
    public function emergency($message, array $context = array())
    {
        return $this->write(self::LEVEL_EMERGENCY,$message,$context);
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
     * @param int $iLevel
     * @param string $sMessage
     * @param array $aContext
     * @return bool
     * @throws \UnexpectedValueException
     */
    private function write($iLevel, $sMessage, array $aContext = array())
    {
        if($this->iLogLevel > $iLevel)
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
                self::$aLevels[$iLevel],
                $sMessage,
                $aContext));

        return true;
    }
}