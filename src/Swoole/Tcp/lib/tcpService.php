<?php


namespace iflow\Swoole\Tcp\lib;


use iflow\Swoole\Tcp\Services;

class tcpService
{

    protected array $events = [
        'connect' => 'onConnect',
        'receive' => 'onReceive',
        'close' => 'onClose',
    ];

    public function initializer(Services $services)
    {
        $services -> eventInit($this, $this->events);
    }

    public function onConnect($server, $fd)
    {}

    public function onReceive($server, $fd, $reactor_id, $data)
    {}

    public function onClose($server, $fd)
    {}
}