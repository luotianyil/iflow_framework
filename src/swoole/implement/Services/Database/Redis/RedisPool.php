<?php

namespace iflow\swoole\implement\Services\Database\Redis;

use Swoole\ConnectionPool;
use Swoole\Database\RedisConfig;
use Swoole\Database\RedisPool as SRedisPool;

class RedisPool {

    protected SRedisPool $redisPool;

    protected RedisConfig $redisConfig;

    public function __construct(protected array $config) {
        $this->redisConfig = new RedisConfig();
        $this -> setRedisPoolConfig($this->config);
        $this->redisPool = new SRedisPool($this->redisConfig, $config['pool_size'] ?? ConnectionPool::DEFAULT_SIZE);
    }

    public function setRedisPoolConfig(array $config): RedisPool {
        foreach ($config['options'] ?? [] as $key => $value) {
            if (is_numeric($key)) $this->redisConfig -> withOption($key, $value);
        }
        foreach ($config as $field => $value) {
            $method = 'with' . ucfirst($field);
            if (method_exists($this->redisConfig, $method)) $this->redisConfig -> {$method}($value);
        }
        return $this;
    }

    /**
     * @return SRedisPool
     */
    public function getRedisPool(): SRedisPool {
        return $this->redisPool;
    }

    /**
     * @return RedisConfig
     */
    public function getRedisConfig(): RedisConfig {
        return $this->redisConfig;
    }

    public function fill(): RedisPool {
        $this->redisPool -> fill();
        return $this;
    }


    public function getConnection(): \Redis {
        return $this->redisPool -> get();
    }

    public function close(?\Redis $redis = null): RedisPool {
        $this->redisPool -> put($redis);
        return $this;
    }

    public function __call(string $name, array $arguments) {
        // TODO: Implement __call() method.
        $redis = $this -> getConnection();
        $result = call_user_func([ $redis, $name ], ...$arguments);
        $this -> close($redis);
        return $result;
    }

}