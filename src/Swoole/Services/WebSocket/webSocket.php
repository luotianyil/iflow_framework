<?php


namespace iflow\Swoole\Services\WebSocket;

use iflow\Swoole\Services\Services;
use iflow\Swoole\Services\WebSocket\socketio\packet;

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

    public string $nsp = '/';

    public function initializer(object $services)
    {
        $this->services = $services;
        $event = $services -> config['event'];
        $services -> eventInit(new $event($this), $this->events);
    }

    public function emit($event, $data)
    {
        $data = packet::create('4'.packet::EVENT . $this->nsp. ',', [
            'data' => [
                $event,
                $data
            ]
        ]) -> toString();
        try {
            if (empty($this->to)) return false;
            foreach ($this->to as $key) {
                $this->services -> getServer() -> push($key, $data);
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