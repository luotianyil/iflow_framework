<?php


namespace iflow\http;

use iflow\http\lib\Request;
use iflow\http\lib\Response;
use iflow\http\lib\service;
use iflow\initializer\appSurroundings;
use iflow\initializer\Config;
use iflow\initializer\Error;
use iflow\initializer\initializer;
use iflow\log\Log;

class App extends \iflow\App {
    protected array $initializers = [
        Config::class,
        Log::class,
        Error::class,
        appSurroundings::class,
        initializer::class
    ];

    protected Request $request;
    protected Response $response;

    protected float $startTime;

    public function initializer(): App {
        $this -> startTime = microtime(true);
        parent::initializer(); // TODO: Change the autogenerated stub
        $this->request = new Request();
        $this->response = new Response();
        $this->request() -> end();
        return $this;
    }

    public function request(): static {
        $initializer = new \iflow\Swoole\Services\Http\lib\initializer();
        $initializer -> services = new service($this);
        $initializer -> __initializer($this->request, $this->response);
        return $this;
    }

    public function end() {
        if (config('app@saveRuntimeLog')) {
            $requestLogs = [
                'requestTime' => date('Y-m-d H:i:s', intval(request() -> server['request_time_float'])),
                'request_uri' => request() -> server['request_uri'],
                'method' => request() -> server['request_method'],
                'runMemoryUsage' => round(memory_get_usage() / 1024 / 1024, 2) . " M",
                'responseTime' => microtime(true) - $this -> startTime . " s"
            ];

            $logInfo = "";
            foreach ($requestLogs as $key => $value) $logInfo .= $key . ": ". $value . " ";
            logs('info', $logInfo);
        }
    }
}