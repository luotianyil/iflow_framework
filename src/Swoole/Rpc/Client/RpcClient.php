<?php

namespace iflow\Swoole\Rpc\Client;

use iflow\App;
use iflow\Swoole\Rpc\Router\CheckRequestRouter;
use iflow\Swoole\Server;
use iflow\Swoole\Services;
use iflow\Swoole\Services\WebSocket\webSocket;
use Swoole\Coroutine\Client;
use Swoole\Process;

class RpcClient {

    use Server;

    protected array $RpcServerConfig = [];
    protected object $RpcServer;

    public App $app;
    public array $config = [];


    protected array $events = [
        RequestEvent::class,
        webSocket::class
    ];


    public function initializer(Services $services): void {
        $this->services = $services;
        $this->app = $services -> app;
        $this->config = $services -> config;

        $this->initializers($services)
            -> initializerClient()
            -> startRpcServer();
    }

    /**
     * 初始化客户端配置
     * @param Services $services
     * @return $this
     */
    public function initializers(Services $services): RpcClient {
        $this->eventType = strtolower($services -> userEvent[1]);
        $this -> setConfig() -> setPid() -> setParam() -> setOptions();
        $this->initializerServer();
        return $this;
    }

    public function initializerClient(): RpcClient {
        $this->connectionRpcServer();
        return $this;
    }

    /**
     * 链接 服务中心 注册服务
     * @return void
     */
    protected function connectionRpcServer() {
        $this->RpcServerConfig = array_values($this->services -> configs['server']);
        $RpcConnection = new RpcConnection($this, $this->config, $this->RpcServerConfig);

        // 创建基础服务
        $process = new Process(function () use ($RpcConnection) {
            $clientName = $this->config['clientName'] ?? 'nil';
            swoole_set_process_name(uniqid('iflow_rpc_client_'. $clientName));

            // 链接 监听服务端传来的数据 以及 发送 心跳包
            $RpcConnection -> connection($this->client, function (mixed $pack) {
                $this->timeSincePing = time();
                $this -> services->callConfigHandle(param: [$this, $pack]);
            });
        });
        $this->server -> addProcess($process);
    }

    /**
     * 启动 RPC 主服务
     * @return void
     */
    protected function startRpcServer() {
        $this->RpcServer = $this->server -> addlistener(...array_values($this->configs['host']));
        $this->RpcServer -> set($this->configs['swConfig']);

        // 注册 服务 事件
        foreach ($this->events as $event) {
            $eventObject = new $event;
            $eventObject -> initializer($this);

            if ($eventObject instanceof RequestEvent) {
                $this->services -> eventInit($event, $eventObject -> events, $this->server);
            }
        }
        $this->server -> start();
    }

    /**
     * 处理已接受的信息
     * @param $server
     * @param mixed $pack
     * @return bool
     */
    public function handle($server, mixed $pack): bool {
        $pack = json_decode($pack, true);
        if (!is_array($pack)) return true;
        return (new CheckRequestRouter()) -> init($server, 0, $pack);
    }

    public function eventInit($class = '', array $event = []) {
        $this->services -> eventInit($class, $event, $this->server);
    }

    /**
     * @return Client
     */
    public function getClient(): Client {
        return $this->client;
    }

    /**
     * @param Client $client
     */
    public function setClient(Client $client): void {
        $this->client = $client;
    }

}