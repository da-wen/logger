<?php
namespace Dawen\Logger\Handler;

use Dawen\Logger\Formatter\FormatterInterface;

interface HandlerInterface
{

    /**
     * gets the current formatter
     *
     * @return FormatterInterface
     */
    public function getFormatter();

    /**
     * handles an entry and returns true or false
     *
     * @param array $aEntry
     * @return bool
     */
    public function handle(array $aEntry);

    /**
     * sets the formatter
     *
     * @param FormatterInterface $oFormatter
     * @return void
     */
    public function setFormatter(FormatterInterface $oFormatter);

}