<?php

namespace iflow\swoole\implement\Server\Rpc\Parsers;

use iflow\swoole\implement\Server\Rpc\Service;
use Swoole\Server;

enum Event: int {

    case register = 0;

    case message = 3;

    case close = 4;

    case ping = 1;

    case pong = 2;

    case connection = 5;

    public function onPacket(array $data, Server $server, int $fd, Service $services) {
        $data['fd'] = $fd;

        return match ($this) {
            Event::register => $server -> send($fd, $services -> consumer -> register($data) ? self::ping -> value : 0),
            Event::ping => $server -> send($fd, Event::pong -> value),
            Event::message => (new Packet($server, $fd, $data)) -> send($services),
            Event::close => $server -> send($fd, $services -> consumer -> remove($fd)),
            Event::pong => $server -> send($fd, Event::ping -> value),
            Event::connection => $server -> send($fd, 200)
        };
    }

}