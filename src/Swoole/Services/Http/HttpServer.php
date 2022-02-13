<?php


namespace iflow\Swoole\Services\Http;

use iflow\initializer\Error;
use iflow\Swoole\Services;

class HttpServer {

    public object $services;

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
     * @param object $request | 请求主体
     * @param object $response | 响应主题
     * @return mixed
     */
    public function onRequest(object $request, object $response): mixed {
        try {
            if ($request->server['path_info'] == '/favicon.ico' || $request->server['request_uri'] == '/favicon.ico') {
                $file = config('app@favicon') ?: '';
                return file_exists($file) ? $response->sendfile($file) : $response -> end();
            }
            event('RequestVerification', $request, $response, $this->services, microtime(true));
        } catch (\Throwable $exception) {
            // 全局异常函数处理
            $this->services -> app -> make(Error::class) -> appHandler($exception);
        }
        return true;
    }
}