<?php


namespace iflow\Swoole\Rpc;


use iflow\facade\Config;
use iflow\Swoole\Rpc\lib\rpcService;

class Services extends \iflow\Swoole\Services\Services
{

    protected object $rpcServer;

    public function run()
    {
        if ($this->userEvent[2] === 'client') {
            $this->initializers = [
                $this->Handle
            ];
        } else {
            $this->rpcServer = $this->getServer() -> addlistener(...array_values($this->config['host']));
            $this->rpcServer -> set(
                $this->config['swConfig']
            );
            Config::delConfigFile($this->config['clientList']['path'] . $this->config['clientList']['name']);
            $this->initializers[] = rpcService::class;
        }
        $this->initializer();
    }

}