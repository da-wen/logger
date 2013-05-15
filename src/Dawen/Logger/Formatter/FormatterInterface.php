<?php

namespace Dawen\Logger\Formatter;

interface FormatterInterface
{

    /**
     * formats an entry
     *
     * @param array $aEntry
     * @return mixed
     */
    public function format(array $aEntry);

}