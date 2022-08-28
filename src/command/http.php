<?php


namespace iflow\command;


use iflow\console\lib\Command;
use iflow\socket\lib\http\Request;
use iflow\socket\lib\http\Response;
use iflow\socket\lib\http\Http as HttpServer;

class http extends Command
{

    protected array $config = [];

    protected float $runMemoryUsage;

    public function handle(array $event = [])
    {
        // TODO: Implement handle() method.
        $this->config = config('socket@http');
        $server = new HttpServer($this->config['host'], $this->config['port'], $this->config);

        // 运行后回调
        $server -> on('afterstart', function ($http) {
            $this -> runMemoryUsage = round(memory_get_usage() / 1024 / 1024, 2);
            $this->Console -> outPut -> writeLine("start iflow FrameWork HTTP SERVER success ...");
            $this->Console -> outPut -> writeLine("start runMemoryUsage {$this -> runMemoryUsage} M");
            $this->Console -> outPut -> writeLine("HTTP SERVER Address: {$http -> host}:{$http -> port}");
        });

        // 请求回调
        $server -> on('request', function (Request $request, Response $response) {
            return event('RequestVerification', $request, $response, microtime(true));
        });
        $server -> start();
    }
}