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

    public function initializer(array $config): static {
        if (!extension_loaded('swoole')) {
            throw new \Exception('Swoole Extension does not exist');
        }
        $this->redis = new SwRedis();
        $this->config = $config;
        $this-> redis -> setOptions($config['options']);
        return $this->connection();
    }
}