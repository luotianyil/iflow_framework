<?php

namespace iflow\swoole\implement\Server\WebSocket\Event;

use iflow\Container\implement\annotation\tools\data\Inject;
use iflow\swoole\implement\Server\WebSocket\PacketPaser\SocketIO\Packet;
use iflow\swoole\implement\Server\WebSocket\WebSocket;
use Swoole\Http\Request;
use Swoole\Server;

class Events {

    protected Server $server;
    protected Request $request;

    public int $fd;
    public string $EIO;
    public string $sid = "";
    public string $nsp = '';

    protected OpenEvent $openEvent;

    #[Inject]
    public Packet $packet;

    public function __construct(
        protected array $config,
        protected WebSocket $webSocket
    ) {}


    public function onMessage(Server $server, $frame) {
        $this->server = $server;
        $data = $this->packet::fromString($frame -> data);
        $this->openEvent -> getPing() -> clearPingTimeOut();

        match (intval($data -> type)) {
            Packet::MESSAGE => $this->Message($data -> data),
            // response
            Packet::PING => $server -> push($this->fd, Packet::PONG),
            Packet::PONG => $this->openEvent -> getPing() -> ping(),
            default => $server -> close($this->fd)
        };
    }


    public function onOpen(Server $server, Request $request) {

        $this->server = $server;
        $this->request = $request;

        $this->fd = $this->request -> fd;
        $this->EIO = $this->request -> get['EIO'] ?? '';

        $this->openEvent = app(OpenEvent::class, [
            $server, $request
        ], true);
        $this->sid = $this->openEvent -> sid;

        $this->openEvent -> onOpen($this->config);
    }

    public function onClose(Server $server, $frame) {
    }


    /**
     * 发送信息
     * @param $data
     * @return bool|mixed
     */
    protected function Message($data): mixed {
        $data = Packet::decode($data);
        $this-> webSocket -> fd = $this->fd;
        $this -> webSocket -> nsp = $data -> nsp;

        $event = match (intval($data -> type)) {
            Packet::CONNECT => $this -> openEvent -> onConnect($data),
            Packet::EVENT => function () use ($data) {
                $this-> webSocket -> emit([
                    'event' => $data -> data [0],
                    'data' => isset($data -> data[1]) ? (
                        json_decode($data -> data[1], JSON_UNESCAPED_UNICODE) ?: $data -> data[1]
                    ) : ''
                ], $this -> config['Handle']);
            },
            default => $this->server -> close($this->fd)
        };
        return is_callable($event) ? call_user_func($event) : true;
    }

}