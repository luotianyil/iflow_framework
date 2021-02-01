<?php


namespace iflow\Swoole\Rpc\lib;

use iflow\Swoole\Server;
use iflow\Swoole\Services\WebSocket\webSocket;
use Swoole\Coroutine\Client as SwooleClient;
use Swoole\Http\Server as HttpServer;
use Swoole\WebSocket\Server as WebSocketServer;

class rpcClient
{

    use Server;
    protected array $rpcBindParam;
    protected object $rpcServer;
    public object $app;
    public array $config = [];

    protected array $events = [
        Event::class,
        webSocket::class
    ];

    // client initializer
    public function initializer($services)
    {
        $this->services = $services;
        $this->app = $services -> app;
        $this->config = $services -> config;

        $this->initializers($services);
        $this->initializerClient();
        $this->startRpcServer();
    }

    public function initializerClient()
    {
        $this->connectionRpcServer();
    }

    public function initializers($services)
    {
        $this->eventType = strtolower($services -> userEvent[1]);
        $this -> setConfig() -> setPid() -> setParam() -> setOptions();
        $this->initializerServer();
    }

    protected function connectionRpcServer()
    {
        $process = new \Swoole\Process(function () {
            $this->client = new SwooleClient(SWOOLE_SOCK_TCP);
            $this->rpcBindParam = array_values($this->services -> configs['server']);
            swoole_set_process_name('rpc_bind_Server');
            \Co\run(
                function () {
                    if ($this->client -> connect(...$this->rpcBindParam)) {
                        $this -> send([
                            'name' => $this->services->config['clientName'],
                            'tcpHost' => $this->services->config['host'],
                            'httpHost' => config('service@host'),
                            'initializer' => 1
                        ]);
                        while ($this->client -> isConnected()) {
                            $pack = $this->client -> recv();
                            if ($pack) {
                                $this->timeSincePing = time();
                                $this -> services->callConfigHandle('', [$this, $pack]);
                            }
                            \Co::sleep(floatval(bcdiv("{$this->services -> config['keep_alive']}", "1000")));
                            $this->ping();
                        }
                    } else {
                        \Co::sleep(floatval(bcdiv("{$this->services -> config['re_connection']}", "1000")));
                    }
                }
            );
        });
        $this->server->addProcess($process);
    }

    protected function startRpcServer()
    {
        $this->rpcServer = $this->server -> addlistener(...array_values($this->configs['host']));
        $this->rpcServer -> set(
            $this->configs['swConfig']
        );

        foreach ($this->events as $key) {
            $event = new $key;
            $event -> initializer($this);

            if ($key === Event::class) {
                $this->eventInit($event, $event -> events);
            }
        }
        $this->server -> start();
    }

    public function handle($server, $pack)
    {
        $pack = json_decode($pack, true);
        if ($pack) {
            $event = new Event();
            $event -> data = $pack;
            $event -> server = $server;
            $event -> isTpc = true;
            return $event -> rpcValidateRouter();
        }
        return $server -> send('404 - no data');
    }

    public function getServer(): HttpServer|WebSocketServer|\Swoole\Server|\Swoole\Coroutine\Client
    {
        return $this->server;
    }

    public function eventInit($class = '', array $event = [])
    {
        $this->services -> eventInit($class, $event, $this->server);
    }
}