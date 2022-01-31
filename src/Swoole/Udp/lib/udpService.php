<?php


namespace iflow\Swoole\Udp\lib;


use iflow\Swoole\Udp\Services;

class udpService
{

    protected Services $services;

    protected array $events = [ 'Packet' => 'onPacket' ];

    public function initializer(Services $services) {
        $this->services = $services;
        $services -> eventInit($this, $this->events);
    }

    public function onPacket($server, $data, $clientInfo) {
        if (class_exists($this->services -> Handle)) {
            call_user_func([new $this->services -> Handle, 'handle'], ...func_get_args());
        }
    }

}