<?php

namespace iflow\swoole\implement\Server\Http\Event;

use iflow\swoole\implement\Server\Tcp\Events\Event as TcpEvent;
use Swoole\Server;

class Event extends TcpEvent {

    protected array $events = [];

    public function onConnect(Server $server, int $fd, int $reactorId): void {
    }

    public function onClose(Server $server, int $fd, int $reactorId): void {
    }

    public function __call(string $name, array $arguments): void {
        // TODO: Implement __call() method.
        $events = $this->services -> getEvents();
        if (in_array($name, $events)) $this -> services -> {$name}(...$arguments);
    }

}