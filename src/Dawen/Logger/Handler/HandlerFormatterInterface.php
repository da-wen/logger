<?php
namespace Dawen\Logger\Handler;

use Dawen\Logger\Formatter\FormatterInterface;

interface HandlerFormatterInterface
{

    /**
     * gets the current formatter
     *
     * @return FormatterInterface
     */
    public function getFormatter();

    /**
     * sets the formatter
     *
     * @param FormatterInterface $oFormatter
     * @return void
     */
    public function setFormatter(FormatterInterface $oFormatter);

}