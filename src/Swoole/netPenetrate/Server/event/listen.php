<?php


namespace iflow\Swoole\netPenetrate\Server\event;


class listen extends event
{

    public function onReceive($server, $fd, $reactor_id, $data)
    {
        // TODO: Implement onReceive() method.
        $this->server -> tunnelChannel -> push(['fd' => $fd, 'data' => $data]);
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
        $this->server -> serverChannel -> push(json_encode(['action' => 'new', 'fd' => $fd]));
    }

    public function onClose($server, $req)
    {
        foreach ($this->server -> table as $tunnelFd => $localFd) {
            if ($localFd == $req) {
                $server->close($tunnelFd);
                break;
            }
        }
    }
}