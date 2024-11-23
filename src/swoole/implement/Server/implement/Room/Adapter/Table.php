<?php

namespace iflow\swoole\implement\Server\implement\Room\Adapter;

use iflow\swoole\implement\Server\implement\Room\RoomAdapterAbstracts;
use Swoole\Table as STable;

/**
 * @mixin STable
 */
class Table extends RoomAdapterAbstracts {

    protected string $cacheClazz = STable::class;

    protected function initializer() {
        // TODO: Implement initializer() method.
        $this->registerRoomFields();
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
        $other['fid'] = serialize(array_unique($fid));
        return $this->cache -> set($room, $other);
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
        $fkey = array_search($fd, $fid);
        unset($fid[$fkey]);

        $roomData = $this->cache -> get($room);
        $roomData['fid'] = serialize($fid);
        return $this->cache -> set($room, $roomData);
    }

    /**
     * 获取房间内所有用户信息
     * @param string $room
     * @return array
     */
    public function getRoomAllUser(string $room): array {
        // TODO: Implement getRoomAllUser() method.
        $fid = $this->cache -> get($room, 'fid');
        return $fid ? unserialize($fid) : [];
    }


    /**
     * 注册房间字段
     * @return void
     */
    public function registerRoomFields(): void {
        $this->options['fields'] = $this->options['fields'] ?? [];

        foreach ($this->options['fields'] as $field) {
            $this->cache -> column($field['name'], $field['type'], $field['size'] ?? 1024);
        }
        $this->cache -> create();
    }
}
