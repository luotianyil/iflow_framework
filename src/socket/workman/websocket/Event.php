<?php


namespace iflow\socket\workman\websocket;


use iflow\swoole\implement\Server\WebSocket\PacketFormatter\SocketIO\PacketFormatter;

class Event
{
    protected array $to = [];

    protected Handle $server;
    protected string $nsp;

    public function handle(Handle $server, array $data, string $nsp): void {
        $this->server = $server;
        $this->nsp = $nsp;
    }

    public function emit($event, $data): bool
    {
        $data = PacketFormatter::create(
            '4'.PacketFormatter::EVENT . $this->nsp . ',', [
                'data' => [
                    $event, $data
                ]
            ]
        ) -> toString();
        if (empty($this->to)) {
            $this->server -> connection -> send($data);
            return true;
        }
        foreach ($this->to as $key) {
            if (isset($this->server -> connections[$key])) {
                $this->server -> getWorker() -> connections[$key] -> send($data);
            }
        }
        $this->to = [];
        return true;
    }

    /**
     * @param array|string $fds
     * @return event
     */
    public function to(array|string $fds): static
    {
        $fds = is_numeric($fds) ? func_get_args() : $fds;
        foreach ($fds as $fd) {
            $this->to[] = $fd;
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getFds(): array
    {
        return $this->to;
    }

    public function getSender(): int
    {
        return $this->server -> id;
    }
}