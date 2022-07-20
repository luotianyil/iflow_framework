<?php

namespace iflow\swoole\implement\Server\WebSocket\Event;

use iflow\swoole\implement\Server\WebSocket\PacketPaser\SocketIO\Packet;
use iflow\swoole\implement\Tools\Ping;
use Swoole\Http\Request;
use Swoole\Server;

class OpenEvent {


    protected Ping $ping;

    public string $sid = '';

    public string $EIO;

    public int $fd;

    public function __construct(protected Server $server, protected Request $request) {
        $this->sid = base64_encode(uniqid(). $this->request -> fd);
        $this->fd = $this->request -> fd;
    }


    /**
     * 初始化链接
     * @param array $config
     * @return bool
     */
    public function onOpen(array $config): bool {

        $this->ping = new Ping(
            $this->server, $this -> request -> fd,
            $config['websocket']['ping_interval'],
            $config['websocket']['ping_timeout']
        );

        $payload = json_encode(
            [
                'sid'          => $this->sid,
                'upgrades'     => [],
                'pingInterval' => $config['websocket']['ping_interval'],
                'pingTimeout'  => $config['websocket']['ping_timeout'],
            ]
        );
        $this->server->push($this->request -> fd, Packet::OPEN . $payload);

        $this->EIO = $this->request -> get['EIO'] ?? '';

        if ($this->EIO < 4) {
            $this->ping -> clearPingTimeOut();
            $this->onConnect();
            return false;
        }

        return $this->ping -> ping();
    }


    public function onConnect(Packet $data = null) {
        $packet = Packet::create(Packet::CONNECT);
        if ($this->EIO >= 4) {
            $packet->data = ['sid' => $this->sid];
        }
        return $this->server -> push(
            $this->fd, $packet::message(
            $packet -> toString(), nsp: $data ? $data -> nsp : '/'
        )
        );
    }

    /**
     * @return Ping
     */
    public function getPing(): Ping {
        return $this->ping;
    }
}