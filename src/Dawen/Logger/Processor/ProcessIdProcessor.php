<?php
/**
 * Created by JetBrains PhpStorm.
 * User: dawen
 * Date: 20.09.13
 * Time: 18:33
 * To change this template use File | Settings | File Templates.
 */

namespace Dawen\Logger\Processor;

class ProcessIdProcessor implements ProcessorInterface
{

    /**
     * don't worry. a static var is much faster
     *
     * @var null
     */
    private static $iProcessId = null;

    public function __construct()
    {
        static::$iProcessId = getmypid();
    }

    /**
     * adds the current process id to the extra array
     *
     * @param array $aEntry
     * @return array
     */
    public function execute(array $aEntry)
    {
        $aEntry['aExtra']['iProcessId'] = static::$iProcessId;

        return $aEntry;
    }

}