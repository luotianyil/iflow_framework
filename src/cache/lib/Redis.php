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
        if (is_string($this->config['sentinel_name']) && $this->config['sentinel_name'] !== '') {
            $this->sentinelToAddress($this->config['sentinel_name']);
        }
        $this->setOptions($config['options']);
        return $this;
    }

    protected function sentinelToAddress(string $sentinel_name)
    {
        $sentinel = $this -> request([
            'SENTINEL', 'get-master-addr-by-name', $sentinel_name
        ]);

        if (!$sentinel) {
            throw new \Exception('redise sentinel of null');
        }
        $this -> close();
        $this -> connect($sentinel[0], $sentinel[1]);
    }
}