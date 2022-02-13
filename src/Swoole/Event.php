<?php


namespace iflow\Swoole;

abstract class Event
{
    public function onConnect($server, $req) {}

    abstract public function onReceive($server, $fd, $reactor_id, $data);

    public function onClose($server, $req) {}

    abstract public function onOpen($server, $req);

    abstract public function onMessage($server, $req);

    abstract public function onConnection($server, $fd);
}