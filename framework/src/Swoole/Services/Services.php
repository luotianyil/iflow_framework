<?php


namespace iflow\Swoole\Services;


use iflow\App;
use iflow\Swoole\Services\Http\HttpServer;
use iflow\Swoole\Services\WebSocket\webSocket;

class Services
{

    protected array $initializers = [
        webSocket::class,
        HttpServer::class
    ];

    public App $app;

    public function handle(App $app)
    {
        $this->app = $app;
        $this->initializer();
    }

    // 启动
    protected function start()
    {
        $this->getServer() -> start();
    }

    // 重启
    protected function reStart()
    {
        $this->getServer() -> reload();
    }

    // 停止
    protected function stop()
    {
        $this->getServer() -> shutdown();
    }

    protected function getServer(): Server
    {
        return $this->app -> make(Server::class);
    }

    public function initializer()
    {
        foreach ($this->initializers as $key) {
            $this->app->make($key) -> initializer($this);
        }
    }

}