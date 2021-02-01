<?php


namespace iflow\cache\lib;

use \Swoole\Coroutine\Redis as SwRedis;

class Redis extends SwRedis
{

    protected array $config = [];

    public function initializer(array $config): self
    {
        $this->config = $config;
        $this->connect($config['host'], $config['port']);
        $this->setOptions($config['options']);
        return $this;
    }

    public function sentinelToAddress()
    {
        return $this -> request([
            'SENTINEL', 'get-master-addr-by-name', $this->config['sentinel_name']
        ]);
    }
}