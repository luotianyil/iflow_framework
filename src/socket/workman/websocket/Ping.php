<?php


namespace iflow\socket\workman\websocket;

use iflow\swoole\implement\Server\WebSocket\PacketFormatter\SocketIO\PacketFormatter;
use iflow\Utils\Tools\Timer;
use Workerman\Connection\TcpConnection;

class Ping
{
    protected mixed $pingTimeoutTimer = null;

    protected mixed $pingIntervalTimer = null;

    public function __construct(
        protected TcpConnection $connection,
        protected float $pingTimer,
        protected float $pingTimeOut
    ){}

    public function ping(): bool
    {
        Timer::clear($this->pingIntervalTimer);
        $this->pingIntervalTimer = Timer::after($this->pingTimer, function () {
            $this->connection->send(PacketFormatter::ping());
            $this->clearPingTimeOut($this->pingTimeOut);
        });
        return true;
    }

    public function clearPingTimeOut($timeout = null)
    {
        Timer::clear($this->pingTimeoutTimer);
        $this->pingTimeoutTimer = Timer::after(
            $timeout === null ? $this->pingTimer + $this->pingTimeOut : $timeout
            , fn () => $this->close());
    }

    public function clear(): bool {
        Timer::clear($this->pingIntervalTimer);
        Timer::clear($this->pingTimeoutTimer);
        return true;
    }

    public function close() {
        // 断开服务
        $this->connection -> close();
    }
}