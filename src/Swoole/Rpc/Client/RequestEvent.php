<?php

namespace iflow\Swoole\Rpc\Client;

use iflow\Swoole\Rpc\Router\CheckRequestRouter;

class RequestEvent {

    public mixed $data = [];
    public object $server;

    public int $fd;

    // Client Server Events
    public array $events = [
        'connect' => 'onConnection',
        'receive' => 'onReceive',
        'close' => 'onClose',
        'task' => 'onTask',
        'request' => 'onRequest'
    ];

    protected object $services;

    public function initializer($services) {
        $this -> services = $services;
    }

    public function onReceive($server, int $fd, $reactor_id, mixed $data) {
        return app(CheckRequestRouter::class, isNew: true) -> init($server, $fd, json_decode($data, true));
    }

    public function onOpen($server, $req) {}

    public function onConnection($server, $fd) {}

    public function onTask() {}

    public function onClose() {}
}