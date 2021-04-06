<?php


namespace iflow\Swoole\dht\lib;


use iflow\Swoole\dht\dht;
use Swoole\Server;

abstract class dhtBase
{

    public dht $dht;
    public Server $server;

    public function initializer(dht $dht) {
        $this->dht = $dht;
        $this->dht -> console -> outPut -> writeLine(
            sprintf('dht %s Ready to start', $dht -> type)
        );
        $this->run() -> start();
    }

    public function bindEvent(object $event, array $events = [])
    {
        foreach ($events as $key => $value) {
            $this->server -> on($key, function () use ($event, $value){
                $event -> {$value}(...func_get_args());
            });
        }
    }

    public function start()
    {
        $this->server -> set($this->dht -> config -> getSwConfig());
        $this->dht -> console -> outPut -> writeLine(
            sprintf(
                "wait startUp dht address: %s:%s",
                $this->server -> host,
                $this->server -> port
            )
        );
        $this->server -> start();
    }

    abstract protected function run(): static;
    abstract public function send($msg, array $options = []): bool;
}