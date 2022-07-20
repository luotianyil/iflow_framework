<?php

namespace iflow\swoole\implement\Tools;

use iflow\swoole\implement\Server\WebSocket\PacketPaser\SocketIO\Packet;
use Swoole\Http\Request;
use Swoole\Server;
use Swoole\Timer;

class Ping {
    protected mixed $pingTimeoutTimer = 0;
    protected mixed $pingIntervalTimer = 0;

    public function __construct(
        protected Server $server,
        protected int $fd,
        protected float $pingTimer,
        protected float $pingTimeOut
    ){}

    public function ping(): bool {
        Timer::clear($this->pingIntervalTimer);
        $this->pingIntervalTimer = Timer::after($this->pingTimer, function () {
            if (!$this->server -> exist($this-> fd)) return false;
            $this->server->push($this -> fd, Packet::ping());
            $this->clearPingTimeOut($this->pingTimeOut);

            return true;
        });
        return true;
    }

    public function clearPingTimeOut($timeout = null) {
        Timer::clear($this->pingTimeoutTimer);
        $this->pingTimeoutTimer = Timer::after(
            $timeout === null ? $this->pingTimer + $this->pingTimeOut : $timeout,
            fn() => $this->close()
        );
    }

    public function clear(): bool {
        Timer::clear($this->pingIntervalTimer);
        Timer::clear($this->pingTimeoutTimer);
        return true;
    }

    public function close(): void {
        // 断开服务
        $this->server -> close($this -> fd);
    }
}