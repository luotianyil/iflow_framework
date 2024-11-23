<?php

namespace iflow\swoole\implement\Server\Proxy\implement\Events;

use Swoole\Server;
use iflow\swoole\implement\Server\Tcp\Events\Event as TCPEvent;

class Event extends TCPEvent {

    public function onReceive(Server $server, int $fd, int $reactorId, string $data): bool {
        $clientInfo = $server -> getClientInfo($fd);

        // PROXY-SERVER
        if ($clientInfo['server_port'] === intval($this->services -> getConfig() -> get('listener@port'))) {
            $server -> task([
                'fd' => $fd,
                'data' => [
                    'remote_fd' => $fd,
                    'server_port' => $clientInfo['server_port'],
                    'server_fd' => $clientInfo['server_fd'],
                    'socket_type' => $clientInfo['socket_type'],
                    'remote_port' => $clientInfo['remote_port'],
                    'remote_ip' => $clientInfo['remote_ip'],
                    'body' => $data
                ]
            ]);
            return true;
        }

        // TUNNEL-SERVER
        if ($clientInfo['server_port'] === intval($this->services -> getConfig() -> get('listeners@tunnel.port'))) {
            return $this -> services -> checkTunnelConnection($server, $fd, $data);
        }

        return true;
    }

    public function onConnect(Server $server, int $fd, int $reactorId): void {
        $clientInfo = $server -> getClientInfo($fd);
        if ($clientInfo['server_port'] === intval($this->services -> getConfig() -> get('listeners@server.port'))) {
            $this -> services -> getTable() -> set('proxy-server', [ 'tunnel_fd' => $fd, 'remote_fd' => 0 ]);
        }
    }

    public function onClose(Server $server, int $fd, int $reactorId): void {
        if ($this -> services -> getTable() -> exists($fd)) $this -> services -> getTable() -> del($fd);
    }

}
