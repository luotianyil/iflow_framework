<?php


namespace iflow\Swoole\dht;


use iflow\console\Console;
use iflow\Swoole\dht\lib\config;

class dht
{

    protected string $nameSpace = "iflow\\Swoole\\dht\\lib\\dht%s";
    public Console $console;

    public function __construct(
        public config $config,
        public $type = 'client'
    ) {
        $this->console = app() -> make(Console::class);
    }

    public function start()
    {
        $class = sprintf($this->nameSpace, $this->type);
        if (class_exists($class)) {
            app($class) -> initializer($this);
        } else {
            $this->console -> outPut -> writeLine('error: dht services not online');
        }
    }

}