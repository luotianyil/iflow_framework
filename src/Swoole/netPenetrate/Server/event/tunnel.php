<?php


namespace iflow\Swoole\netPenetrate\Server\event;


class tunnel extends event
{

    public function onReceive($server, $fd, $reactor_id, $data)
    {
        // TODO: Implement onReceive() method.
        if (!$this->server -> table -> get($fd)['local_fd']) {
            $this->server-> table -> set($fd, ['local_fd' => $data]);
        } else {
            $this->server -> localChannel -> push([
                'fd' => $this->server -> table -> get($fd)['local_fd'],
                'data' => $data
            ]);
        }
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
    }

    public function onClose($server, $req)
    {
        foreach ($this->server -> table as $tunnelFd => $localFd) {
            if ($localFd == $req) {
                $server->close($localFd);
                break;
            }
        }
    }
}