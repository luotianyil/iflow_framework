<?php


namespace iflow\cache\lib;


use \Swoole\Coroutine\Redis as SwRedis;

/**
 * Class Redis
 * @mixin SwRedis
 * @package iflow\cache\lib
 */
class Redis extends IRedis
{

    protected array $config = [];
    protected object $redis;

    public function initializer(array $config): static
    {
        if (!extension_loaded('swoole')) {
            throw new \Exception('Swoole Extension does not exist');
        }
        $this->redis = new SwRedis();
        $this->config = $config;
        $this-> redis -> connect($config['host'], $config['port']);
        if (isset($this->config['sentinel_name']) && is_string($this->config['sentinel_name']) && $this->config['sentinel_name'] !== '') {
            $this->sentinelToAddress($this->config['sentinel_name']);
        }
        $this-> redis -> setOptions($config['options']);
        return $this;
    }

    protected function sentinelToAddress(string $sentinel_name)
    {
        $sentinel = $this-> redis -> request([
            'SENTINEL', 'get-master-addr-by-name', $sentinel_name
        ]);
        if (!$sentinel) {
            throw new \Exception('redise sentinel of null');
        }
        $this -> redis -> close();
        $this -> redis -> connect($sentinel[0], $sentinel[1]);
    }
}