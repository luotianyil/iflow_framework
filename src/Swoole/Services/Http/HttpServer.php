<?php


namespace iflow\Swoole\Services\Http;

use iflow\initializer\Error;
use iflow\Swoole\Services;
use iflow\Swoole\Services\Http\lib\initializer;

class HttpServer extends initializer
{
    /**
     * 初始化HTTP服务
     * @param Services $services
     */
    public function initializer(Services $services) {
        $this->services = $services;
        // 初始化 HTTP 全局环境
        $services->getServer()->on('request', function ($request, $response) {
            $this->onRequest($request, $response);
        });
    }

    /**
     * 请求回调
     * @param $request | 请求主体
     * @param $response | 响应主题
     * @return mixed
     */
    public function onRequest($request, $response): mixed
    {
        try {
            $startTime = microtime(true);
            if ($request->server['path_info'] == '/favicon.ico' || $request->server['request_uri'] == '/favicon.ico') {
                $file = config('app@favicon') ?: '';
                return file_exists($file)
                    ? $response->sendfile($file)
                    : $response -> end();
            }

            // 进入路由处理
            $this -> __initializer($request, $response);

            // 请求是否写入 日志
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
        } catch (\Throwable $exception) {
            // 全局异常函数处理
            $this->services -> app -> make(Error::class) -> appHandler($exception);
        }
        return true;
    }
}