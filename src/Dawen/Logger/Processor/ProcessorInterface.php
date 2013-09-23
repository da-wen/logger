<?php
/**
 * Created by JetBrains PhpStorm.
 * User: dawen
 * Date: 20.09.13
 * Time: 18:29
 * To change this template use File | Settings | File Templates.
 */

namespace Dawen\Logger\Processor;

interface ProcessorInterface
{
    /**
     * executes the processor and return formatted array
     *
     * @param array $aEntry
     * @return array
     */
    public function execute(array $aEntry);
}