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

    /** @var int|null  */
    private $iProcessId = null;

    public function __construct()
    {
        $this->iProcessId = getmypid();
    }

    /**
     * adds the current process id to the extra array
     *
     * @param array $aEntry
     * @return array
     */
    public function execute(array $aEntry)
    {
        $aEntry['aExtra']['iProcessId'] = $this->iProcessId;

        return $aEntry;
    }

}