<?php


namespace iflow\Swoole\Services\Http;

use iflow\Swoole\Services\Http\lib\initializer;
use iflow\Swoole\Services\Services;

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
                $file = $this->services -> app -> getRootPath() . 'public' . DIRECTORY_SEPARATOR . 'favicon.ico';
                if (file_exists($file)) $response->sendfile($file);
                else $response -> end();
                return;
            }
            $this->services -> app -> make(initializer::class) -> __initializer($this -> services, $request, $response);

            $request_time = date('Y-m-d H:i:s',$request -> server['request_time_float']);
            logs('info',
                "requestTime: {$request_time} url: {$request -> server['request_uri']} method: {$request -> server['request_method']}");
        });
    }
}