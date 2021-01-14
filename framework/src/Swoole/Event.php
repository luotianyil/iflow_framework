<?php


namespace iflow\Swoole;

trait Event
{
    public function onConnect($server, $req)
    {}

    public function onReceive($server, $fd, $reactor_id, $data)
    {}

    public function onClose($server, $req)
    {}

    public function onOpen($server, $req)
    {}

    public function onMessage($server, $req)
    {}

    public function onConnection($server)
    {}
}