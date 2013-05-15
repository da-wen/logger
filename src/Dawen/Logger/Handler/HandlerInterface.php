<?php
namespace Dawen\Logger\Handler;

interface HandlerInterface
{

    /**
     * handles an entry and returns true or false
     *
     * @param array $aEntry
     * @return bool
     */
    public function handle(array $aEntry);

}