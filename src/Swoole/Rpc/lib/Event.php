<?php


namespace iflow\Swoole\Rpc\lib;

use iflow\Swoole\Rpc\lib\router\checkRequest;

class Event
{

    public mixed $data = [];
    public object $server;
    public int $fd = 0;
    public array $events = [
        'connect' => 'onConnection',
        'receive' => 'onReceive',
        'close' => 'onClose',
        'task' => 'onTask',
        'request' => 'onRequest'
    ];

    protected object $services;

    public function initializer($services)
    {
        $this -> services = $services;
    }

    public function onReceive($server, $fd, $reactor_id, $data): bool
    {
        return (new checkRequest())
            -> init($server, $fd, json_decode($data, true));
    }

    public function onOpen($server, $req)
    {
        // TODO: Implement onOpen() method.
    }

    public function onConnection($server, $fd)
    {}

    public function onTask()
    {}

    public function onClose()
    {}
}