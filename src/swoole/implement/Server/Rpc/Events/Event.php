<?php

namespace iflow\swoole\implement\Server\Rpc\Events;

use iflow\swoole\implement\Server\Rpc\Service;
use iflow\swoole\implement\Tools\Ping;
use Swoole\Server;

class Event {

    protected Ping $ping;

    public function __construct(protected Service $servicesAbstract) {
    }

    public function onReceive(Server $server, int $fd, int $reactorId, string $data) {
        $packet = json_decode($data, true) ?: [];

        if (empty($packet) || !isset($packet['event'])) {
            return $server -> send($fd, 403);
        }

        return \iflow\swoole\implement\Server\Rpc\Parsers\Event::from($packet['event']) -> onPacket(
            $packet, $server, $fd, $this -> servicesAbstract
        );
    }

    public function onConnect(Server $server, int $fd, int $reactorId) {
    }

    public function onClose(Server $server, int $fd, int $reactorId) {
        $this->servicesAbstract -> consumer -> remove($fd);
    }

    public function onPacket(Server $server, string $data, array $clientInfo) {
    }

}