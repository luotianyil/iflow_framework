<?php

namespace iflow\swoole\implement\Services\Dht\implement\Parser;

use iflow\swoole\abstracts\ServicesAbstract;
use Swoole\Server;

enum PacketEnum: string {

    case PING = 'PING';

    case FIND_NODE = 'FIND_NODE';

    case GET_PEERS = 'GET_PEERS';

    case ANNOUNCE_PEER = 'ANNOUNCE_PEER';

    public function onPacket(array $data, Server $server, array $clientInfo, ServicesAbstract $service) {
        $packetClass = $service -> getServicesCommand() -> config -> get('handle', Packet::class);
        $packet = new $packetClass($server, $data, $clientInfo, $service);

        return match ($this) {
            PacketEnum::PING => $packet -> ping(),
            PacketEnum::FIND_NODE => $packet -> findNode($data),
            PacketEnum::GET_PEERS => $packet -> getPeers($data),
            PacketEnum::ANNOUNCE_PEER => $packet -> announcePeer($data)
        };
    }
}