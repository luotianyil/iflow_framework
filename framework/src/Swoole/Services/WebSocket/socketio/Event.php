<?php


namespace iflow\Swoole\Services\WebSocket\socketio;


use Swoole\Http\Request;
use Swoole\WebSocket\Server;

class Event
{

    public function onOpen($server, $req)
    {
        echo $req -> fd.PHP_EOL;
    }

    public function onClose($server, $req)
    {
    }

    public function onMessage(Server $server, $req)
    {
        $server->push($req -> fd, '42'.json_encode(['message', '123123']));
    }

    public function onConnection($server)
    {
        echo $server -> id;
    }

}