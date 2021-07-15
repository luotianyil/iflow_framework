<?php


namespace iflow\cache\lib;


use Redis;

class IRedis
{

    protected array $config = [];
    protected object $redis;

    public function initializer(array $config): static
    {
        if (!extension_loaded('redis')) {
            throw new \Exception('Redis Extension does not exist');
        }
        $this->redis = new Redis();
        $this->config = $config;
        return $this->connection();
    }

    protected function connection(): static
    {
        $this->redis -> connect(
            $this->config['host'], $this->config['port']
        );
        if (isset($this->config['sentinel_name']) && is_string($this->config['sentinel_name']) && $this->config['sentinel_name'] !== '') {
            $this->sentinelToAddress($this->config['sentinel_name']);
        }
        return $this;
    }

    protected function sentinelToAddress(string $sentinel_name)
    {
        $sentinel = $this->redis -> rawCommand(...[
            'SENTINEL', 'get-master-addr-by-name', $sentinel_name
        ]);

        if (!$sentinel) {
            throw new \Exception('Redis Sentinel does not exist');
        }

        $this->redis -> close();
        $this->redis -> connect(
            $sentinel[0], $sentinel[1]
        );
    }

    /**
     * 设置数据
     * @param string $name key
     * @param mixed $value value
     * @param float $expire 过期时间
     * @return bool
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
     */
    public function pull(string $name): mixed
    {
        $result = $this-> get($name);
        if ($result) {
            $this->redis -> del($name);
        }
        return $result;
    }

    /**
     * 获取redis数据
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function get(string $name, mixed $default = ''): mixed
    {
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
     */
    public function clear(): bool
    {
        return $this->redis -> flushDB();
    }

    /**
     * 验证数据是否存在
     * @param string $name
     * @return bool
     */
    public function has(string $name): bool
    {
        return $this->redis -> exists($name);
    }

    public function __call(string $name, array $arguments)
    {
        // TODO: Implement __call() method.
        if (!method_exists($this -> redis, $name)) return null;
        return call_user_func([
            $this->redis, $name
        ], ...$arguments);
    }
}