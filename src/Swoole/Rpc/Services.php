<?php


namespace iflow\Swoole\Rpc;


use iflow\facade\Cache;
use iflow\Swoole\Rpc\lib\rpcService;

class Services extends \iflow\Swoole\Services\Services
{

    protected object $rpcServer;

    public function run() {
        if ($this->userEvent[2] === 'client') {
            $this->initializers = [
                $this->Handle
            ];
        } else {
            $this->rpcServer = $this->getServer() -> addlistener(...array_values($this->config['host']));
            $this->rpcServer -> set(
                $this->config['swConfig']
            );

            config('swoole.rpc', [
                'server' => [
                    'enable' => true
                ]
            ]);

            Cache::store($this->config['clientList']) -> delete($this->config['clientList']['cacheName']);
            $this->initializers[] = rpcService::class;
        }
        $this->initializer();
    }

}