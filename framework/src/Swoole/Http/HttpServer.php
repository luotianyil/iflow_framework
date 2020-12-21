<?php


namespace iflow\Swoole\Http;

use iflow\App;
use iflow\Middleware;
use Swoole\Http\Server;

class HttpServer
{

    protected Server $http;

    protected App $app;

    protected array $config = [
        'port' => 8080,
        'daemonize' => false
    ];

    public function initializer(App $app)
    {

        $this->app = $app;
        $this->config = config('swoole');

        // 初始化 HTTP 全局环境
        $this->http = new Server('127.0.0.1', 8080);
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
        $this->app -> make(Middleware::class) -> initializer($this->app);
    }
}