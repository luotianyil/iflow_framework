<?php


namespace iflow\Swoole\netPenetrate\Server\event;


use iflow\Swoole\netPenetrate\Server\Server;

abstract class event extends \iflow\Swoole\Event
{

    public function __construct(
        protected Server $server
    ) {}
}