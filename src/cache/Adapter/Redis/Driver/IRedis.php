<?php

namespace iflow\cache\Adapter\Redis\Driver;

use iflow\cache\Adapter\Redis\Exceptions\RedisOptionException;
use Redis;
use \Swoole\Coroutine\Redis as SwRedis;

/**
 * @mixin Redis
 */
class IRedis {

    /**
     * Redis 连接句柄
     * @var Redis|SwRedis
     */
    public Redis|SwRedis $handle;

    protected array $config = [];

    protected string $connectType = 'pconnect';

    /**
     * @param array $config 连接配置
     * @throws RedisOptionException
     */
    public function initializer(array $config): static {

        $config['driver'] = $config['driver'] ?? 'Redis';
        $this->connectType = $config['connectType'] ?? 'pconnect';

        $this->handle = $config['driver'] === 'Redis' ? new Redis() : new SwRedis();
        $this->config = $config;
        $this -> setOptions($this->config['options'] ?? []) -> Connection();
        return $this;
    }

    /**
     * 设置配置
     * @param array $options
     * @return static
     * @throws RedisOptionException|\RedisException
     */
    public function setOptions(array $options): static {
        if ($this->config['driver'] === 'SRedis') {
            $this->handle -> setOptions($options);
            return $this;
        }

        foreach ($options as $optionName => $optionValue) {
            is_int($optionName)
                ? $this->handle -> setOption($optionName, $optionValue)
                : throw new RedisOptionException('optionsKey must be a Number');
        }

        return $this;
    }

    protected function Connection(): static {

        call_user_func([ $this->handle, $this->connectType ], $this->config['host'], $this->config['port'], $this->config['timeout'] ?? 30);

        if (!empty($this->config['sentinel_name']) && is_string($this->config['sentinel_name'])) {
            $this->sentinelToAddress($this->config['sentinel_name']);
        }

        if (key_exists('auth', $this->config) && $this->config['auth']['pass'] !== '') {
            $this->authLogin($this->config['auth']) ?: throw new \RuntimeException('Redis Auth Verification Failed');
        }

        return $this;
    }

    /**
     * 通过哨兵获取 Redis 地址
     * @throws \Exception
     */
    protected function sentinelToAddress(string $sentinel_name): void {
        $sentinel = $this->request('SENTINEL', 'get-master-addr-by-name', $sentinel_name);

        if (!$sentinel) {
            throw new \Exception('Redis Sentinel does not exist');
        }

        $this->handle -> close();
        call_user_func([ $this->handle, $this->connectType ], $this->config['host'], $this->config['port'], $this->config['timeout'] ?? 30);
    }

    /**
     * Redis rawCommand
     * @return mixed
     * @throws \RedisException
     */
    public function request(): mixed {
        $args = func_get_args();
        if (method_exists($this->handle, 'request')) {
            return $this->handle -> request($args);
        }
        return $this->handle -> rawCommand(...$args);
    }

    /**
     * 设置数据
     * @param string $name key
     * @param mixed $value value
     * @param float $expire 过期时间
     * @return bool
     * @throws \RedisException
     */
    public function set(string $name, mixed $value, float $expire = 0): bool
    {
        $value = is_numeric($value) ? $value  : serialize($value);
        return $expire > 0
            ? $this->handle -> setex($name, $expire, $value)
            : $this->handle -> set($name, $value);
    }

    /**
     * 读取后并删除
     * @param string $name
     * @return mixed
     * @throws \RedisException
     */
    public function pull(string $name): mixed
    {
        $result = $this-> get($name);
        if ($result) {
            $this->handle -> del($name);
        }
        return $result;
    }

    /**
     * 获取handle数据
     * @param string $name
     * @param mixed $default
     * @return mixed
     * @throws \RedisException
     */
    public function get(string $name, mixed $default = ''): mixed {
        $result = $this->handle -> get($name);

        if (false === $result || is_null($result)) {
            return $default;
        }
        return $result ? (
            is_numeric($result) ? $result : unserialize($result)
        ) : $result;
    }

    /**
     * 清除缓存
     * @return bool
     * @throws \RedisException
     */
    public function clear(): bool {
        return $this->handle -> flushDB();
    }

    /**
     * 验证数据是否存在
     * @param string $name
     * @return bool
     * @throws \RedisException
     */
    public function has(string $name): bool {
        return $this->handle -> exists($name);
    }

    /**
     * Redis 登录
     * @param array $auth
     * @return bool
     * @throws \RedisException
     */
    public function authLogin(array $auth): bool {
        if (empty($auth['user'])) unset($auth['user']);
        if ($this->config['driver'] === 'SRedis') $auth = $auth['pass'];
        return $this->handle -> auth($auth);
    }

    public function __call(string $name, array $arguments) {
        // TODO: Implement __call() method.
        if (!method_exists($this -> handle, $name)) return null;
        return call_user_func([ $this->handle, $name ], ...$arguments);
    }
}