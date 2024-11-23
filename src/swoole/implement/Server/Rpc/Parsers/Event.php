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

        $packetClass = $services -> getServicesCommand() -> config -> get('packet');
        $packet = new $packetClass($server, $fd, $data);

        return match ($this) {
            Event::register => $server -> send($fd, $packet -> register($services, $data)),
            Event::ping => $server -> send($fd, Event::pong -> value),
            Event::message => $packet -> send($services),
            Event::close => $packet -> close($server, $services, $fd),
            Event::pong => $server -> send($fd, Event::ping -> value),
            Event::connection => $server -> send($fd, 200)
        };
    }

}