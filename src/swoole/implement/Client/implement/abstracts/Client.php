<?php

namespace iflow\swoole\implement\Client\implement\abstracts;

use iflow\swoole\abstracts\ServicesAbstract;
use iflow\Utils\Tools\Timer;

abstract class Client {

    protected \Swoole\Client $client;

    public function __construct(protected array $serverConfig, protected ?ServicesAbstract $services = null) {
        $this->client = new \Swoole\Client($this->serverConfig['mode'] ?? SWOOLE_TCP);
    }

    protected function Connection() {
        Timer::tick(intval($this -> serverConfig['re_connection']), function (int $timer_id) {
            if ($this->client -> connect($this->serverConfig['host'], $this->serverConfig['port'])) {
                Timer::clear($timer_id);
                return $this->wait();
            }
            $this->services ?-> getServicesCommand() -> Console -> outWrite('Connection FAIL errCode: ' . $this->client -> errCode);
        });
    }

    abstract protected function wait();

    protected function onPacket(string $data) {
    }

    public function send(mixed $data): mixed {
        return $this -> client -> send(
            !is_string($data) && !is_numeric($data)
                ? json_encode($data, JSON_UNESCAPED_UNICODE)
                : $data
        );
    }
}