<?php


namespace iflow\Swoole\Services\WebSocket;

use iflow\Swoole\Services\WebSocket\socketio\Parser;

class webSocket
{

    public array $events = [
        'message' => 'onMessage',
        'close' => 'onClose',
        'open' => 'onOpen'
    ];

    private array $to = [];

    public object $services;

    public int $fd = 0;

    public function initializer($services)
    {
        $this->services = $services;
        $event = config('service@Handle');
        $services -> eventInit(new $event($this), $this->events);
    }

    public function emit($event, $data)
    {
        try {
            if (empty($this->to)) return false;
            foreach ($this->to as $key) {
                $this->services -> getServer() -> push($key, '42'.Parser::encode($event, $data));
            }
            return true;
        } finally {
            $this->to = [];
        }
    }

    public function to($fds): self
    {
        $fds = is_numeric($fds) || is_integer($fds) ? func_get_args() : $fds;
        foreach ($fds as $fd) {
            $this->to[] = $fd;
        }
        return $this;
    }

    public function getFds(): array
    {
        return $this->to;
    }

    public function getSender(): int
    {
        return $this->fd;
    }
}