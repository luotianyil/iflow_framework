<?php

namespace iflow\swoole\implement\Server\implement\Room\Adapter\Interfaces;

interface RoomAdapterInterface {

    /**
     * 加入房间
     * @param string $room
     * @param int $fd
     * @param array $other
     * @return bool
     */
    public function join(string $room, int $fd, array $other): bool;

    /**
     * 退出房间
     * @param string $room
     * @param int $fd
     * @return bool
     */
    public function quit(string $room, int $fd): bool;

    /**
     * 发送消息
     * @param string $room
     * @param mixed $data
     * @return bool
     */
    public function emit(string $room, mixed $data): bool;

    /**
     * 获取房间内所有用户信息
     * @param string $room
     * @return array
     */
    public function getRoomAllUser(string $room): array;

}
