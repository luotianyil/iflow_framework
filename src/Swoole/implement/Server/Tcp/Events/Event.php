<?php

namespace iflow\swoole\implement\Server\Tcp\Events;

use iflow\swoole\abstracts\ServicesAbstract;
use iflow\swoole\implement\Tools\Ping;
use Swoole\Server;

class Event {

    protected Ping $ping;

    public function __construct(protected ServicesAbstract $servicesAbstract) {
    }

    public function onStart(Server $server) {
    }

    public function onReceive(Server $server, int $fd, int $reactorId, string $data) {
        $this->ping -> ping();
    }

    public function onConnect(Server $server, int $fd, int $reactorId) {
        $this->ping = new Ping($server, $fd, ...[
            $this->servicesAbstract -> getServicesCommand() -> config -> get('heartbeat@ping_interval'),
            $this->servicesAbstract -> getServicesCommand() -> config -> get('heartbeat@ping_timeout')
        ]);
    }

    public function onClose(Server $server, int $fd, int $reactorId) {
        $this->ping -> clear();
    }

    public function onPipeMessage(Server $server, int $src_worker_id, mixed $message) {
    }
    
}