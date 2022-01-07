<?php


namespace iflow\command;


use iflow\console\lib\Command;
use iflow\http\lib\service;
use iflow\socket\lib\http\request;
use iflow\socket\lib\http\response;
use iflow\Swoole\Services\Http\HttpServer;

class http extends Command
{

    protected array $config = [];
    protected HttpServer $httpServer;

    public function handle(array $event = [])
    {
        // TODO: Implement handle() method.
        $this->config = config('socket@http');
        $server = new \iflow\socket\lib\http\http(
            $this->config['host'],
            $this->config['port'],
            $this->config
        );

        $this->httpServer = new HttpServer();

        $this->httpServer -> services = new service($this -> app);
        // 运行后回调
        $server -> on('afterstart', function ($http) {
            $this->httpServer -> services -> runMemoryUsage = round(memory_get_usage() / 1024 / 1024, 2);
            $this->Console -> outPut -> writeLine("start iflow FrameWork HTTP SERVER success ...");
            $this->Console -> outPut -> writeLine("start runMemoryUsage {$this->httpServer -> services -> runMemoryUsage} M");
            $this->Console -> outPut -> writeLine("HTTP SERVER Address: {$http -> host}:{$http -> port}");
        });

        // 请求回调
        $server -> on('request', function (request $request, response $response) {
            $this->httpServer -> onRequest($request, $response);
        });
        $server -> start();
    }
}