<?php


namespace iflow\Swoole\Tcp\lib;

use Swoole\Server;

class tcpService
{
    protected object $services;
    public array $events = [
        'connect' => 'onConnect',
        'receive' => 'onReceive',
        'close' => 'onClose',
    ];

    public function initializer($services)
    {
        $this->services = $services;
        $services -> eventInit($this, $this->events);
    }

    public function onConnect($server, $fd)
    {}

    public function onReceive(Server $server, $fd, $reactor_id, $data)
    {
        if (class_exists($this->services -> Handle)) {
            call_user_func([new $this->services -> Handle, 'handle'], ...func_get_args());
        }
    }

    public function onClose($server, $fd)
    {}
}