<?php

namespace iflow\swoole\implement\Server\implement\Room;

use Exception;
use iflow\Container\Container;
use iflow\Container\implement\generate\exceptions\InvokeClassException;
use iflow\swoole\implement\Server\implement\Room\Adapter\Redis;
use iflow\swoole\implement\Server\implement\Room\Adapter\Table;

class Room {

    /**
     * 缓存房间数据
     * @var array|string[]
     */
    protected array $cacheEnum = [
        'table' => Table::class,
        'redis' => Redis::class
    ];

    protected Table|Redis $table;

    public function __construct(protected string $roomType, protected object $server, protected array $options) {
        $this->createRoomCache($this->options['cache']);
    }


    /**
     * 获取缓存类型对象
     * @param string $type
     * @return Table|Redis
     * @throws InvokeClassException
     * @throws Exception
     */
    protected function createRoomCache(string $type): Table|Redis {
        $clazz = $this->cacheEnum[$type] ?? '';

        if (!class_exists($clazz))
            throw new Exception('ROOM CACHE TYPE UNDEFINED');

        return $this->table = Container::getInstance() -> make(
            $clazz, [ $this->roomType, $this->server, $this->options ]
        ) -> get($this->roomType, true);
    }
}