<?php


namespace iflow\Swoole\netPenetrate\Server\event;

class server extends event
{

    public function onReceive($server, $fd, $reactor_id, $data)
    {
        // TODO: Implement onReceive() method.
    }

    public function onOpen($server, $req)
    {
        // TODO: Implement onOpen() method.
    }

    public function onMessage($server, $req)
    {
        // TODO: Implement onMessage() method.
    }

    public function onConnection($server, $fd)
    {
        // TODO: Implement onConnection() method.
        go(function () use ($server, $fd) {
            while (true) {
                $data = $this->server -> serverChannel ->pop();
                $server->send($fd, $data . PHP_EOL);
            }
        });
    }
}