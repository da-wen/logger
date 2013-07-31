<?php
namespace Dawen\Logger\Handler;

interface DebugHandlerInterface extends HandlerInterface
{

    /**
     * gets the collected data
     *
     * @return array
     */
    public function getData();


}