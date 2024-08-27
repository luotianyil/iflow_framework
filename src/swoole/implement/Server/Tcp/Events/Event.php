<?php

namespace iflow\swoole\implement\Server\Tcp\Events;

use iflow\swoole\abstracts\ServicesAbstract;
use iflow\swoole\implement\Server\WebSocket\PacketFormatter\SocketIO\PacketFormatter;
use iflow\swoole\implement\Tools\Ping;
use Swoole\Server;

class Event {

    protected Ping $ping;

    public function __construct(protected ServicesAbstract $services) {
    }

    public function onStart(Server $server): void {
    }

    public function onReceive(Server $server, int $fd, int $reactorId, string $data): mixed {
        if (intval($data) === PacketFormatter::PONG) return $this->ping -> ping();
        return true;
    }

    public function onPacket(Server $server, string $data, array $clientInfo): void {
    }

    public function onConnect(Server $server, int $fd, int $reactorId): void {
        $this->ping = new Ping($server, $fd, ...[
            $this->services -> getServicesCommand() -> config -> get('heartbeat@ping_interval', -1),
            $this->services -> getServicesCommand() -> config -> get('heartbeat@ping_timeout', -1)
        ]);
    }

    public function onClose(Server $server, int $fd, int $reactorId): void {
        $this->ping -> clear();
    }

    public function onPipeMessage(Server $server, int $src_worker_id, mixed $message): void {
    }

    protected function getClientInfo(int $fd): array {
        return $this -> services -> getSwService() -> getClientInfo($fd);
    }
    
}