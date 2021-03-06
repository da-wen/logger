<?php
/**
 * everything with op cache (Zend_Optimizer)
 *
 * tested with separate isHandling function, without Formatter object
 * time for 500 = 0.0043s - 0.0046s
 * time for 1 = 0.00086s
 *
 * tested with no isHandling function, without Formatter Object
 * time for 500 = 0.0039s - 0.0042s
 * time for 1 = 0.00080s - 0.00082s
 */

namespace Dawen\Logger;

use Psr\Log\LoggerInterface;
use Dawen\Logger\Handler\HandlerInterface;
use Dawen\Logger\Processor\ProcessorInterface;

class AdvancedLogger implements LoggerInterface
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
     * name of the logger
     *
     * @var null|string
     */
    private $sName = null;

    /**
     * @var null|HandlerInterface
     */
    private $oHandler = null;

    /**
     * @var null|ProcessorInterface
     */
    private $oProcessor = null;

    /**
     * constructor of logger
     *
     * @param string $sName
     * @param null|string $sDateTimeFormat
     */
    public function __construct($sName,$sDateTimeFormat = null)
    {
        $this->sName = $sName;
        $this->sDateTimeFormat =  (null !== $sDateTimeFormat) ? $sDateTimeFormat : self::DATETIME_FORMAT;
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $iLevel
     * @param string $sMessage
     * @param array $aContext
     * @param array $aExtra
     * @return bool|null
     */
    public function log($iLevel, $sMessage, array $aContext = array(), array $aExtra = array())
    {
        return $this->writeEntry($iLevel, $sMessage, $aContext, $aExtra);
    }

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param string $sMessage
     * @param array $aContext
     * @param array $aExtra
     * @return bool|null
     */
    public function alert($sMessage, array $aContext = array(), array $aExtra = array())
    {
        return $this->writeEntry(self::LEVEL_ALERT, $sMessage, $aContext, $aExtra);
    }

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param string $sMessage
     * @param array $aContext
     * @param array $aExtra
     * @return bool|null
     */
    public function critical($sMessage, array $aContext = array(), array $aExtra = array())
    {
        return $this->writeEntry(self::LEVEL_CRITICAL, $sMessage, $aContext, $aExtra);
    }

    /**
     * Detailed debug information.
     *
     * @param string $sMessage
     * @param array $aContext
     * @param array $aExtra
     * @return bool|null
     */
    public function debug($sMessage, array $aContext = array(), array $aExtra = array())
    {
        return $this->writeEntry(self::LEVEL_DEBUG, $sMessage, $aContext, $aExtra);
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string $sMessage
     * @param array $aContext
     * @param array $aExtra
     * @return bool|null
     */
    public function error($sMessage, array $aContext = array(), array $aExtra = array())
    {
        return $this->writeEntry(self::LEVEL_ERROR, $sMessage, $aContext, $aExtra);
    }

    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @param string $sMessage
     * @param array $aContext
     * @param array $aExtra
     * @return bool|null
     */
    public function info($sMessage, array $aContext = array(), array $aExtra = array())
    {
        return $this->writeEntry(self::LEVEL_INFO, $sMessage, $aContext, $aExtra);
    }

    /**
     * Normal but significant events.
     *
     * @param string $sMessage
     * @param array $aContext
     * @param array $aExtra
     * @return bool|null
     */
    public function notice($sMessage, array $aContext = array(), array $aExtra = array())
    {
        return $this->writeEntry(self::LEVEL_NOTICE, $sMessage, $aContext, $aExtra);
    }

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param string $sMessage
     * @param array $aContext
     * @param array $aExtra
     * @return bool|null
     */
    public function warning($sMessage, array $aContext = array(), array $aExtra = array())
    {
        return $this->writeEntry(self::LEVEL_WARNING, $sMessage, $aContext, $aExtra);
    }

    /**
     * System is unusable.
     *
     * @param $sMessage
     * @param array $aContext
     * @param array $aExtra
     * @return bool
     */
    public function emerg($sMessage, array $aContext = array(), array $aExtra = array())
    {
        return $this->writeEntry(self::LEVEL_EMERGENCY, $sMessage, $aContext, $aExtra);
    }

    /**
     * System is unusable.
     *
     * @param string $sMessage
     * @param array $aContext
     * @param array $aExtra
     * @return bool|null
     */
    public function emergency($sMessage, array $aContext = array(), array $aExtra = array())
    {
        return $this->writeEntry(self::LEVEL_EMERGENCY, $sMessage, $aContext, $aExtra);
    }

    /**
     * gets handler instance
     *
     * @return HandlerInterface|null
     */
    public function getHandler()
    {
        return $this->oHandler;
    }

    /**
     * sets handler
     *
     * @param HandlerInterface $oHandler
     */
    public function setHandler(HandlerInterface $oHandler)
    {
        $this->oHandler = $oHandler;
    }

    /**
     * sets the processor for this logger
     *
     * @param ProcessorInterface $oProcessor
     *
     * @return void
     */
    public function setProcessor(ProcessorInterface $oProcessor)
    {
        $this->oProcessor = $oProcessor;
    }

    /**
     * writes message to file (appending)
     *
     * @param int $iLevel
     * @param string $sMessage
     * @param array $aContext
     * @param array $aExtra
     * @return bool
     */
    private function writeEntry($iLevel, $sMessage, array $aContext = array(), array $aExtra = array())
    {
        $_oDateTime = new \DateTime();
        //create entry
        $_aEntry = array(
            'sTimestamp'        => $_oDateTime->format($this->sDateTimeFormat),
            'iLevel'            => $iLevel,
            'sLevel'            => self::$aLevels[$iLevel],
            'sLoggerName'       => $this->sName,
            'sMessage'          => (string)$sMessage,
            'aContext'          => $aContext,
            'aExtra'            => $aExtra
        );

        //execute processor if set
        if(null !== $this->oProcessor)
        {
            $_aEntry = $this->oProcessor->execute($_aEntry);
        }

        return $this->oHandler->handle($_aEntry);
    }
}