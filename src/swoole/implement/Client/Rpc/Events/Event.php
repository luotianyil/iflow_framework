<?php

namespace iflow\swoole\implement\Client\Rpc\Events;

use iflow\swoole\abstracts\ServicesAbstract;
use iflow\swoole\implement\Commounity\Rpc\Request\Routers\CheckRequestRouter;
use Swoole\Server;

class Event {

    public function __construct (protected ServicesAbstract $servicesAbstract) {
    }

    public function onReceive(Server $server, int $fd, $reactor_id, mixed $data): bool
    {
        return app(CheckRequestRouter::class, isNew: true) -> init($server, $fd, json_decode($data, true));
    }

}