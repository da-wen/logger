<?php
namespace Dawen\Logger\Formatter;

class LineFormatter implements FormatterInterface
{
    const LOG_TEMPLATE = '[%sTimestamp%] %sLoggerName%.%sLevel%: %sMessage% [%aContext%] [%aExtra%]';

    /**
     * formats the log entry and returns the line.
     *
     * @param array $aEntry
     * @return string
     */
    public function format(array $aEntry)
    {

        $_sOutput = self::LOG_TEMPLATE;

        foreach($aEntry as $_sVarName => $_mVar)
        {
            if(is_array($_mVar))
            {
                $_mVar = (!empty($_mVar)) ? json_encode($_mVar) : '';
            }
            $_sOutput = str_replace('%'.$_sVarName.'%', $_mVar, $_sOutput);
        }

        return $_sOutput.PHP_EOL;
    }
}