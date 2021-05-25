<?php


namespace iflow\Swoole\Services\WebSocket\socketio\lib;


use iflow\Swoole\Services\WebSocket\socketio\packet;
use Swoole\Http\Request;
use Swoole\Server;
use Swoole\Timer;

class Ping
{

    protected mixed $pingTimeoutTimer = null;
    protected mixed $pingIntervalTimer = null;

    public function __construct(
        protected Server $server,
        protected Request $request,
        protected float $pingTimer,
        protected float $pingTimeOut
    ){}

    public function ping() {
        Timer::clear($this->pingIntervalTimer);
        $this->pingIntervalTimer = Timer::after($this->pingTimer, function () {
            $this->server->push($this->request -> fd, packet::ping());
            $this->clearPingTimeOut($this->pingTimeOut);
        });
        return true;
    }

    public function clearPingTimeOut($timeout = null)
    {
        Timer::clear($this->pingTimeoutTimer);
        $this->pingTimeoutTimer = Timer::after(
            $timeout === null ? $this->pingTimer + $this->pingTimeOut : $timeout
            , function () {
            $this->close();
        });
    }

    public function clear(): bool
    {
        Timer::clear($this->pingIntervalTimer);
        Timer::clear($this->pingTimeoutTimer);
        return true;
    }

    public function close()
    {
        $this->server -> close($this->request -> fd);
    }
}