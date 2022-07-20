<?php

namespace iflow\swoole\implement\Server\implement;

use iflow\Container\Container;
use iflow\swoole\implement\Tools\Tables;
use Swoole\Table;

class Room {

    protected Table $table;

    public function __construct(string $roomType, protected object $server) {
        $this->table = Container::getInstance() -> make(Tables::class) -> get($roomType, true);
        $this->table -> column('fid', Table::TYPE_STRING, 2048);
    }

    /**
     * 加入房间
     * @param string $room
     * @param int $fd
     * @param array $other
     * @return bool
     */
    public function join(string $room, int $fd, array $other = []): bool {
        $fid = $this->getRoomFid($room);
        $fid[] = $fd;
        $other['fid'] = serialize(array_unique($fid));
        return $this->table -> set($room, $other);
    }

    /**
     * 退出房间
     * @param string $room
     * @param int $fd
     * @return bool
     */
    public function quit(string $room, int $fd): bool {
        $fid = $this->getRoomFid($room);
        $fkey = array_search($fd, $fid);
        unset($fid[$fkey]);

        $roomData = $this->table -> get($room);
        $roomData['fid'] = serialize($fid);
        return $this->table -> set($room, $roomData);
    }

    /**
     * 发送信息
     * @param string $room
     * @param array $data
     * @return bool
     */
    public function emit(string $room, array $data = []): bool {
        $fid = $this->getRoomFid($room);
        if (empty($fid)) return false;

        foreach ($fid as $id) {
            $this->server -> send($id, $data);
        }

        return true;
    }

    /**
     * 获取房间全部用户标识
     * @param string $room
     * @return mixed
     */
    public function getRoomFid(string $room): array {
        $fid = $this->table -> get($room, 'fid');
        return $fid ? unserialize($fid) : [];
    }

    /**
     * 添加字段
     * @param array $field
     * @return bool
     */
    public function addField(array $field): bool {
        return $this->table -> column($field['name'], $field['type'], $field['size'] ?? 0);
    }

    /**
     * @return Table
     */
    public function getTable(): Table {
        return $this->table;
    }
}