<?php


namespace iflow\Swoole\Services\Http;

use iflow\Middleware;
use iflow\Swoole\Services\Services;
use Swoole\Server;

class HttpServer
{

    protected Server $http;

    protected Services $services;

    protected array $config = [
        'port' => 8080,
        'daemonize' => false
    ];

    public function initializer(Services $services)
    {

        $this->services = $services;
        $this->config = config('swoole') ?: $this->config;

        // 初始化 HTTP 全局环境
        $this->http = new Server('127.0.0.1', $this->config['port']);
        $this->http -> set($this->config);

        $this->http->on('start', function ($server) {
            $this->httpStart($server);
        });
        $this->http->on('request', function ($request, $response) {
            $this->httpRequest($request, $response);
        });
        $this->http -> start();
    }

    // http服务启动项 - 回调
    public function httpStart($server)
    {}

    // http请求回调
    public function httpRequest($request, $response)
    {
        // 初始化中间件
        $this->services -> app -> make(Middleware::class) -> initializer($this->services -> app);
    }
}