<?php

namespace iflow\swoole\implement\Server\implement\Room\Adapter;

use iflow\swoole\implement\Server\implement\Room\RoomAdapterAbstracts;
use iflow\cache\Adapter\Redis\Redis as CRedis;


class Redis extends RoomAdapterAbstracts {

    protected string $cacheClazz = CRedis::class;

    protected function initializer() {
        // TODO: Implement initializer() method.
        $this->cache -> initializer(config(
            sprintf('cache@stores.%s', $this->options['redis-cache'])
        ));
    }

    /**
     * 加入房间
     * @param string $room
     * @param int $fd
     * @param array $other
     * @return bool
     */
    public function join(string $room, int $fd, array $other): bool {
        // TODO: Implement join() method.
        $fid = $this->getRoomAllUser($room);
        $fid[] = $fd;
        return $this->cache -> set($room, array_unique($fid));
    }

    /**
     * 退出房间
     * @param string $room
     * @param int $fd
     * @return bool
     */
    public function quit(string $room, int $fd): bool {
        // TODO: Implement quit() method.
        $fid = $this->getRoomAllUser($room);

        if (!in_array($fd, $fid)) return true;
        unset($fid[array_search($fd, $fid)]);

        return $this->cache -> set($room, $fid);
    }

    /**
     * 获取房间内所有用户信息
     * @param string $room
     * @return array
     */
    public function getRoomAllUser(string $room): array {
        // TODO: Implement getRoomAllUser() method.
        return $this->cache -> get(
            sprintf('%s-room-%s', $this->roomType, $room)
        );
    }
}
