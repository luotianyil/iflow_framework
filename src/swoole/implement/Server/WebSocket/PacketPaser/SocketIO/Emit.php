<?php

namespace iflow\swoole\implement\Server\WebSocket\PacketPaser\SocketIO;

use iflow\swoole\implement\Server\WebSocket\WebSocket;

class Emit {

    public function __construct(protected WebSocket $webSocket, mixed $data) {
    }

    public function handle(): mixed {
        return true;
    }

    public function to(int|array $fds): Emit {
        $fds = is_numeric($fds) || is_integer($fds) ? func_get_args() : $fds;
        foreach ($fds as $fd) {
            if (in_array($fd, $this->webSocket -> to)) continue;
            $this->webSocket -> to[] = $fd;
        }
        return $this;
    }

    public function getFds(): array {
        return $this->webSocket -> to;
    }

    public function getSender(): int {
        return $this->webSocket -> fd;
    }

    /**
     * 向指定客户端发送消息
     * @param string $event
     * @param mixed $data
     * @return bool
     */
    public function emit(string $event, mixed $data): bool {
        return $this->webSocket -> sender($event, $data);
    }
}