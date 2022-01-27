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

    public function run() {
        $this->initializer();
    }

    // HTTP 服务 异步任务回调函数
    public function onTask($serv, $task_id, $reactor_id, $data)
    {}

    // 异步投递执行完毕回调
    public function onFinish($serv, $task_id, $data)
    {}
}