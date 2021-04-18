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
        $startTime = microtime(true);
        if ($request->server['path_info'] == '/favicon.ico' || $request->server['request_uri'] == '/favicon.ico') {
            $file = config('app@favicon') ?: '';
            if (file_exists($file)) $response->sendfile($file);
            else $response -> end();
            return;
        }
        $this -> __initializer($request, $response);

        if (config('app@saveRuntimeLog')) {
            $requestLogs = [
                'requestTime' => date('Y-m-d H:i:s', $request -> server['request_time_float']),
                'request_uri' => $request -> server['request_uri'],
                'method' => $request -> server['request_method'],
                'runMemoryUsage' => round(memory_get_usage() / 1024 / 1024, 2) - $this->services -> runMemoryUsage. " M",
                'responseTime' => microtime(true) - $startTime . " s"
            ];

            $logInfo = "";
            foreach ($requestLogs as $key => $value) {
                $logInfo .= $key . ": ". $value . " ";
            }
            logs('info', $logInfo);
        }
    }
}