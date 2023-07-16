<?php

namespace iflow\swoole\implement\Server\Rpc\Cluster;

use iflow\cache\Adapter\Redis\Redis;
use iflow\Container\Container;
use iflow\swoole\implement\Tools\Tables;
use Swoole\Table;

class Cache {

    protected static Table|Redis $consumers;

    protected string $type;

    public function __construct(protected array $config) {
        $this->getConsumersCache();
    }

    protected function getConsumersCache(): static {
        $this->type = $this->config['cluster']['type'] ?? 'table';
        static::$consumers =
            $this->type === 'table'
                ? Container::getInstance() -> make(Tables::class) -> get('rpc_consumer', true)
                : \iflow\facade\Cache::store('redis');

        if ($this->type === 'table') {
            foreach ($this->config['cluster']['fields'] as $field) {
                self::$consumers -> column($field['name'], $field['type'], $field['size'] ?? 0);
            }
            self::$consumers -> create();
        }

        return $this;
    }

    public static function add(int $fd, array $data): bool {
        if (empty($data['fd']) || empty($data['host'])) return false;

        $data['host'] = json_encode($data['host'], true);

        self::$consumers -> exists($fd) && self::$consumers -> delete($fd);
        return self::$consumers -> set($data['name'], [ 'name' => $fd ]) && self::$consumers -> set($fd, $data);
    }

    public static function get(int $fd): array {
        return self::$consumers -> get($fd) ?: [];
    }

    /**
     * 通过客户端名称获取 客户端信息
     * @param string $name
     * @return array
     * @throws \RedisException
     */
    public static function getConsumerByName(string $name): array {
        $client = self::$consumers -> get($name) ?: [];
        if (empty($client)) return [];
        return self::get($client['name']);
    }

    /**
     * @param int $fd
     * @return bool|int
     * @throws \RedisException
     */
    public static function del(int $fd): bool|int {
        $client = self::get($fd);
        return self::$consumers -> delete($fd) && self::$consumers -> delete($client['name']);
    }
}