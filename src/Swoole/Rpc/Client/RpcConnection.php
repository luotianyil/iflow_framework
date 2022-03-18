<?php

namespace iflow\Swoole\Rpc\Client;

use Swoole\Coroutine\Client;
use function Co\run;

class RpcConnection {


    public function __construct(
        protected RpcClient $rpcClient,
        protected array $config = [],
        protected array $RpcServerConfig = []
    ) {}

    public function connection(&$client, callable $call): RpcConnection {
        $this->RpcServerConfig[2] = $this->RpcBindParams[2] ?? SWOOLE_TCP;
        $client = new Client(array_pop($this->RpcServerConfig));
        $this->rpcClient -> setClient($client);

        run(function () use (&$client, $call) {
            if ($client -> connect(...$this->RpcServerConfig)) {
                $this -> rpcClient -> send([
                    'name' => $this -> config['clientName'],
                    'tcpHost' => $this -> config['host'],
                    'httpHost' => config('swoole.service@host'),
                    'initializer' => 1
                ]);

                $this -> rpcClient -> app -> register(Client::class, $client);
                $this->wait($this -> rpcClient, $call);
            } else {
                \Co::sleep(floatval(bcdiv("{$this -> config['re_connection']}", "1000")));
            }
        });
        return $this;
    }


    public function wait(RpcClient $rpcClient, callable $call) {
        $client = $rpcClient -> getClient();
        while ($client -> isConnected()) {
            $pack = $client -> recv();
            if ($pack) $call($pack);

            \Co::sleep(floatval(bcdiv("{$this -> config['keep_alive']}", "1000")));
            $rpcClient->ping();
        }
    }
}