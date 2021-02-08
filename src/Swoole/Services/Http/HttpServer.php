<?php


namespace iflow\Swoole\Services\Http;

use iflow\Swoole\Services\Http\lib\initializer;

class HttpServer extends initializer
{
    protected array $config = [
        'port' => 8080,
        'daemonize' => false
    ];

    public function initializer($services)
    {
        $this->services = $services;
        $this->config = config('swoole') ?: $this->config;
        // 初始化 HTTP 全局环境
        $services->getServer()->on('request', function ($request, $response) {
            $this->onRequest($request, $response);
        });
    }

    public function onRequest($request, $response)
    {
        if ($request->server['path_info'] == '/favicon.ico' || $request->server['request_uri'] == '/favicon.ico') {
            $file = config('app@favicon');
            if (file_exists($file)) $response->sendfile($file);
            else $response -> end();
            return;
        }
        $this -> __initializer($request, $response);
        $request_time = date('Y-m-d H:i:s', $request -> server['request_time_float']);
        logs('info',
            "requestTime: {$request_time} url: {$request -> server['request_uri']} method: {$request -> server['request_method']}");
    }
}