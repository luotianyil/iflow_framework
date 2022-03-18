<?php


namespace iflow\Swoole\Rpc;


use iflow\facade\Cache;
use iflow\Swoole\Rpc\Server\RpcService;

class Services extends \iflow\Swoole\Services\Services
{

    protected object $rpcServer;

    protected array $rpcServerInit = [
        'server' => [
            'enable' => true
        ]
    ];

    public function run() {
        if ($this->userEvent[2] === 'client') {
            $this->initializers = [ $this->Handle ];
        } else {
            $this->rpcServer = $this->getServer() -> addlistener(...array_values($this->config['host']));
            $this->rpcServer -> set($this->config['swConfig']);

            config('swoole.rpc', $this->rpcServerInit);
            Cache::store($this->config['clientList']) -> delete($this->config['clientList']['cacheName']);
            $this->initializers[] = RpcService::class;
        }
        $this->initializer();
    }
}