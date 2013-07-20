<?php
namespace Dawen\Logger\Handler;

use Dawen\Logger\Formatter\LineFormatter;
use Dawen\Logger\Formatter\FormatterInterface;

class StreamFormatterHandler implements HandlerInterface, HandlerFormatterInterface
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
     * @var null
     */
    private $oFormatter = null;

    /**
     * resource for file stream
     *
     * @var resource
     */
    private $rStream = null;

    public function __construct($iLogLevel, $sFilePath)
    {
        $this->iLogLevel  = $iLogLevel;
        $this->sFilePath = $sFilePath;
    }

    /**
     * @inheritdoc
     *
     * @return FormatterInterface|LineFormatter|null
     */
    public function getFormatter()
    {
        if(null === $this->oFormatter)
        {
            $this->oFormatter = new LineFormatter();
        }
        return $this->oFormatter;
    }

    /**
     * @inheritdoc
     *
     * @param array $aEntry
     * @return bool
     * @throws \UnexpectedValueException
     */
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

        $aEntry['formatted'] = $this->getFormatter()->format($aEntry);
        fwrite($this->rStream, $aEntry['formatted']);

        return true;

    }

    /**
     * @inheritdoc
     *
     * @param FormatterInterface $oFormatter
     */
    public function setFormatter(FormatterInterface $oFormatter)
    {
        $this->oFormatter = $oFormatter;
    }

}