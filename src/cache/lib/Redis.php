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
        if ($this->config['sentinel_name'] !== '') {
            $this->sentinelToAddress();
        }
        $this->setOptions($config['options']);
        return $this;
    }

    protected function sentinelToAddress()
    {
        $sentinel = $this -> request([
            'SENTINEL', 'get-master-addr-by-name', $this->config['sentinel_name']
        ]);

        if (!$sentinel) {
            throw new \Exception('redise sentinel of null');
        }
        $this -> close();
        $this -> connect($sentinel[0], $sentinel[1]);
    }
}