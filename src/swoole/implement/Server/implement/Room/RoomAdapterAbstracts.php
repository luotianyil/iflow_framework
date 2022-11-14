<?php

namespace iflow\swoole\implement\Server\implement\Room;

use iflow\Container\Container;
use iflow\swoole\implement\Server\implement\Room\Adapter\Interfaces\RoomAdapterInterface;

abstract class RoomAdapterAbstracts implements RoomAdapterInterface {

    protected object $cache;

    protected string $cacheClazz = '';

    public function __construct(
        protected string $roomType,
        protected object $server,
        protected array $options
    ) {
        $this->cache =
            Container::getInstance() -> make($this->cacheClazz)
                -> get($this->roomType, true);

        $this->initializer();
    }

    abstract protected function initializer();

    /**
     * 向客户端推送消息
     * @param string $room
     * @param mixed $data
     * @return bool
     */
    public function emit(string $room, mixed $data): bool {
        // TODO: Implement emit() method.
        $userList = $this->getRoomAllUser($room);
        if (empty($userList)) return false;

        array_map(function ($id) use ($data) {
            return $this->server -> send($id, $data);
        }, $userList);

        return true;
    }


    public function __call(string $name, array $arguments): mixed {
        // TODO: Implement __call() method.
        return call_user_func([ $this->cache, $name ], ...$arguments);
    }

    /**
     * @return mixed
     */
    public function getCache(): object {
        return $this->cache;
    }
}