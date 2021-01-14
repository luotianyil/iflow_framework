<?php


namespace iflow\Swoole;


use iflow\Swoole\lib\pid;
use Swoole\Http\Server as HttpServer;
use Swoole\WebSocket\Server as WebSocketServer;
use \Swoole\Coroutine\Client as SwooleClient;

trait Client
{

    protected HttpServer|WebSocketServer|\Swoole\Server|SwooleClient $server;
    protected Services $services;
    public pid $pid;
    protected string $eventType = '';
    public array $param = [];
    public array $configs = [];
    public string $Handle = '';
    public array $options = [];

    public function initializerClient()
    {
        if ($this->eventType !== 'mqtt') {
            $this->server = new SwooleClient($this->eventType === 'udp' ? SWOOLE_SOCK_UDP : SWOOLE_SOCK_TCP);
            $this->monitorClient();
        }
    }

    public function monitorClient()
    {
        \Co\run(function () {
            if (!$this->server->connect(...$this->param)) {
                $this->services -> Console -> outPut -> writeLine("connect failed. Error: {$this->server->errCode}");
            } else {
                while($this->server -> isConnected()) {
                    $pack = $this->server -> recv();
                    if (null !== $pack) {
                        if (class_exists($this->services -> Handle)) {
                            call_user_func([new $this->services -> Handle, 'handle'], ...[$this, $pack]);
                        }
                    }
                }
            }
        });
    }
}