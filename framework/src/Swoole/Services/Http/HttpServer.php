<?php


namespace iflow\Swoole\Services\Http;

use iflow\Swoole\Services\Http\lib\initializer;
use iflow\Swoole\Services\Services;
use iflow\Swoole\Services\WebSocket\socketio\SocketIo;

class HttpServer
{

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
        $services->getServer()->on('request', function ($request, $response) {
            if ($request->server['path_info'] == '/favicon.ico' || $request->server['request_uri'] == '/favicon.ico') {
                $file = $this->services -> app -> getAppPath() . 'public' . DIRECTORY_SEPARATOR . 'favicon.ico';
                if (file_exists($file)) $response->sendfile($file);
                else $response -> end();
                return;
            }
            $this->services -> app -> make(initializer::class) -> __initializer($this -> services, $request, $response);
        });
    }

    // http请求回调
    public function httpRequest($request, $response)
    {
        // 初始化中间件
        $url = explode('/', trim($request->server['request_uri'], '/'));
        if ($this->config['websocket']['enable']) {
            if ($url[0] === 'socket.io') {
                $SocketIo = new SocketIo();
                $SocketIo -> config = $this->config['websocket'];
                $url = $SocketIo-> __initializer($request, $response);
            }
        }
    }
}