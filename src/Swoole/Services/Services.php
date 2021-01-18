<?php


namespace iflow\Swoole\Services;

use iflow\Swoole\Services\Http\HttpServer;
use iflow\Swoole\Services\WebSocket\webSocket;

class Services extends \iflow\Swoole\Services
{

    protected array $event = [
        'start' => 'onStart',
        'task' => 'onTask'
    ];
    protected array $initializers = [
        webSocket::class,
        HttpServer::class
    ];

    public function run()
    {
        $this->initializer();
    }

}