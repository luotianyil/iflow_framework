<?php

namespace iflow\swoole\implement\Server\Udp\Events;

use Swoole\Server;

class Event extends \iflow\swoole\implement\Server\Tcp\Events\Event {

    public function onPacket(Server $server, string $data, array $clientInfo) {
        $this->ping -> ping();
    }

}