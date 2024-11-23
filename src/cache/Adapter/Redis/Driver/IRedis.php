<?php

namespace iflow\cache\Adapter\Redis\Driver;

use iflow\cache\Adapter\AdapterInterface;
use iflow\cache\Adapter\Redis\Exceptions\RedisOptionException;
use iflow\swoole\implement\Services\Database\Redis\RedisPool;
use Redis;

/**
 * @mixin Redis
 */
class IRedis implements AdapterInterface
{

    /**
     * Redis 连接句柄
     * @var Redis|RedisPool
     */
    public Redis|RedisPool $handle;

    protected \Redis|RedisPool $redis;

    protected array $config = [];

    protected string $connectType = 'pconnect';

    /**
     * @param array $config 连接配置
     * @throws RedisOptionException|\RedisException
     */
    public function initializer(array $config): IRedis {

        $config['driver'] = $config['driver'] ?? 'Redis';
        $this->connectType = $config['connectType'] ?? 'pconnect';

        $this->handle = $config['driver'] === 'Redis' ? new Redis() : app(RedisPool::class, [ $config ]);
        $this->redis = $this->handle;

        $this->config = $config;

        if ($this->config['driver'] === 'Redis') {
            $this -> setOptions($this->config['options'] ?? []) -> Connection();
        }

        return $this;
    }


    public function getRedis(): \Redis {
        return $this->redis = $this->handle instanceof RedisPool
            ? $this->handle -> getConnection()
            : $this->handle;
    }


    /**
     * 设置配置
     * @param array $options
     * @return IRedis
     * @throws RedisOptionException|\RedisException
     */
    public function setOptions(array $options): IRedis {
        foreach ($options as $optionName => $optionValue) {
            is_int($optionName)
                ? $this->redis -> setOption($optionName, $optionValue)
                : throw new RedisOptionException('optionsKey must be a Number');
        }

        return $this;
    }

    /**
     * 初始化连接
     * @return $this
     * @throws \RedisException
     */
    protected function Connection(): IRedis {
        call_user_func([ $this->redis, $this->connectType ], $this->config['host'], $this->config['port'], $this->config['timeout'] ?? 30);

        if (!empty($this->config['sentinel_name']) && is_string($this->config['sentinel_name'])) {
            $this->sentinelToAddress($this->config['sentinel_name']);
        }

        if (key_exists('auth', $this->config) && !empty($this->config['auth'])) {
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

        $this->redis -> close();
        call_user_func([ $this->redis, $this->connectType ], $this->config['host'], $this->config['port'], $this->config['timeout'] ?? 30);
    }

    /**
     * Redis rawCommand
     * @return mixed
     * @throws \RedisException
     */
    public function request(): mixed {
        return $this->redis -> rawCommand(...func_get_args());
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
            ? $this->redis -> setex($name, $expire, $value)
            : $this->redis -> set($name, $value);
    }

    /**
     * 读取后并删除
     * @param string $name
     * @return mixed
     * @throws \RedisException
     */
    public function pull(string $name): mixed {
        $result = $this-> get($name);
        if ($result) $this->redis -> del($name);
        return $result;
    }

    /**
     * 获取redis数据
     * @param string $name
     * @param mixed $default
     * @return mixed
     * @throws \RedisException
     */
    public function get(string $name, mixed $default = ''): mixed {
        $result = $this->redis -> get($name);

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
        return $this->redis -> flushDB();
    }

    /**
     * 验证数据是否存在
     * @param string $name
     * @return bool
     * @throws \RedisException
     */
    public function has(string $name): bool {
        return $this->redis -> exists($name);
    }

    /**
     * Redis 登录
     * @param string $auth
     * @return bool
     * @throws \RedisException
     */
    public function authLogin(string $auth): bool {
        return $this->redis -> auth($auth);
    }

    public function close(): void {
        if ($this->handle instanceof RedisPool) {
            $this -> handle -> close($this->redis);
            return;
        }
        $this->handle -> close();
    }

    public function __call(string $name, array $arguments) {
        // TODO: Implement __call() method.
        if (!method_exists($this->redis, $name)) return null;
        return call_user_func([ $this->redis, $name ], ...$arguments);
    }

}