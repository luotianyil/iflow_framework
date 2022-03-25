<?php


namespace iflow\Swoole;


use iflow\Swoole\lib\Pid;
use Swoole\Http\Server as HttpServer;
use Swoole\WebSocket\Server as WebSocketServer;
use Swoole\Coroutine\Client as SwooleClient;
use function Co\run;

trait Client
{

    protected HttpServer|WebSocketServer|\Swoole\Server|SwooleClient $server;
    protected ?SwooleClient $client = null;
    protected Services $services;
    public Pid $pid;
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

    /**
     * 链接客户端
     * @return void
     */
    public function monitorClient() {
        run(function () {
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

    /**
     * 发送心跳包
     * @param mixed $data
     * @return bool|mixed
     */
    public function ping(mixed $data = 1): mixed {
        if (isset($this->services -> config['keep_alive']) && $this -> timeSincePing < (time() - $this->services -> config['keep_alive'])) {
            $buffer = $this->send($data);
            if ($buffer) $this -> timeSincePing = time();
            else {
                return $this->client -> close();
            }
        }
        return true;
    }

    /**
     * 向服务端发送数据
     * @param mixed $data
     * @return mixed
     */
    public function send(mixed $data): mixed {
        return $this -> client -> send(
            match (!is_string($data) && !is_numeric($data)) {
                true => json_encode($data, JSON_UNESCAPED_UNICODE),
                default => $data
            }
        );
    }
}