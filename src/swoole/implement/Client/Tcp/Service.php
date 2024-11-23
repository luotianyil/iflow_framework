<?php

namespace iflow\swoole\implement\Client\Tcp;

use iflow\swoole\abstracts\ServicesAbstract;
use Swoole\Coroutine\Client;

class Service extends ServicesAbstract {

    protected function connection(string $connection): Client {
        $config = $this -> config -> get($connection);

        $client = new Client($config['mode']);
        $client -> set(
            $config['swConfig'] ?? $this -> config -> get('swConfig')
        );

        $client -> connect($config['host'], $config['port'], $config['timeout'] ?? 0.5);
        return $client;
    }

}