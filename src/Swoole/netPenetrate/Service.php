<?php


namespace iflow\Swoole\netPenetrate;

use \Swoole\Coroutine\Client;
use iflow\command\netPenetrate;

abstract class Service
{

    public Table $table;

    public mixed $server;
    public mixed $tunnel;
    public mixed $listen;

    public Client $client;

    protected array $events = [
        'receive' => 'onReceive',
        'open' => 'onOpen',
        'connect' => 'onConnection',
        'close' => 'onClose',
    ];

    public function __construct(
        protected array $config = [],
        public ?netPenetrate $netPenetrate = null
    ) {
        $this->table = new Table(1024 * 10);
        $this->table -> column('local_fd', Table::TYPE_INT);
        $this->table -> create();
    }

    abstract public function start();

    protected function on($server, $event)
    {
        foreach ($this->events as $key => $value) {
            $server -> on($key, function () use ($event, $value) {
                call_user_func([$event, $value], ...func_get_args());
            });
        }
    }

    protected function outServerAddress () {
        $this->netPenetrate -> Console -> outPut -> writeLine("Start Server Success");
        foreach ($this->config as $key => $value) {
            if (isset($value['host']) && isset($value['port'])) {
                $this->netPenetrate -> Console -> outPut -> writeLine("$key Address: ${value['host']}:${value['port']}");
            }
        }
    }
}