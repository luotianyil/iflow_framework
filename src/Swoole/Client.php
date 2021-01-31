<?php


namespace iflow\Swoole;


use iflow\Swoole\lib\pid;
use Swoole\Http\Server as HttpServer;
use Swoole\WebSocket\Server as WebSocketServer;
use \Swoole\Coroutine\Client as SwooleClient;

trait Client
{

    protected HttpServer|WebSocketServer|\Swoole\Server|SwooleClient $server;
    protected SwooleClient $client;
    protected Services $services;
    public pid $pid;
    protected string $eventType = '';
    public array $param = [];
    public array $configs = [];
    public string $Handle = '';
    public array $options = [];

    protected int $timeSincePing = 0;

    public function initializerClient()
    {
        if ($this->eventType !== 'mqtt' && $this->eventType !== 'rpc') {
            $this->client = new SwooleClient($this->eventType === 'udp' ? SWOOLE_SOCK_UDP : SWOOLE_SOCK_TCP);
            $this->monitorClient();
        }
    }

    public function monitorClient()
    {
        \Co\run(function () {
            if (!$this->client->connect(...$this->param)) {
                $this->services -> Console -> outPut -> writeLine("connect failed. Error: {$this->client->errCode}");
            } else {
                while($this->client -> isConnected()) {
                    $pack = $this->client -> recv();
                    if ($pack) {
                        $this->timeSincePing = time();
                        $this -> services->callConfigHandle('', [$this, $pack]);
                    }
                    $this->ping();
                }
            }
        });
    }

    public function ping($data = 1)
    {
        if (isset($this->services -> config['keep_alive']) && $this -> timeSincePing < (time() - $this->services -> config['keep_alive'])) {
            $buffer = $this->send($data);
            if ($buffer) $this -> timeSincePing = time();
            else $this->client -> close();
        }
    }

    public function send($data)
    {
        return $this -> client -> send(
            match (!is_string($data)) {
                true => json_encode($data, JSON_UNESCAPED_UNICODE),
                default => $data
            }
        );
    }
}