<?php

namespace iflow\Swoole\Rpc\Server;

use iflow\Swoole\Tcp\lib\tcpService;

class RpcService extends tcpService {
    public function onConnect($server, $fd) {
        if (class_exists($this->services -> config['connection'])) {
            call_user_func([new $this->services -> config['connection'], 'onConnect'], ...func_get_args());
        }
    }

    public function onClose($server, $fd) {
        if (class_exists($this->services -> config['connection'])) {
            call_user_func([new $this->services -> config['connection'], 'onClose'], ...func_get_args());
        }
    }
}