<?php


namespace iflow\Swoole\Services\WebSocket\socketio;


use iflow\Swoole\Services\WebSocket\socketio\lib\Ping;
use iflow\Swoole\Services\WebSocket\webSocket;
use Swoole\Http\Request;
use Swoole\Server;

class Event
{
    protected array $config = [];
    protected webSocket $websocket;
    public Packet $packet;

    public Server $server;
    public Request $request;
    public Ping $ping;

    public int $fd;
    public string $EIO;
    public string $sid = "";
    public string $nsp = '';

    public function __construct(webSocket $websocket)
    {
        $this->websocket = $websocket;
        $this->config = $websocket -> services -> config;
        $this->packet = new Packet();
    }

    public function onOpen(Server $server, Request $frame)
    {
        $this->fd = $frame -> fd;
        $this->EIO = $frame -> get['EIO'] ?? '';
        $this -> ping = new Ping(
            $server, $frame,
            $this->config['websocket']['ping_interval'],
            $this->config['websocket']['ping_timeout']
        );

        $this->server = $server;
        $this->request = $frame;

        $this->sid = base64_encode(uniqid());

        $payload = json_encode(
            [
                'sid'          => $this->sid,
                'upgrades'     => [],
                'pingInterval' => $this->config['websocket']['ping_interval'],
                'pingTimeout'  => $this->config['websocket']['ping_timeout'],
            ]
        );
        $server->push($this->fd, Packet::OPEN . $payload);
        if ($this->EIO < 4) {
            $this -> ping -> clearPingTimeOut();
            $this->onConnect();
        } else {
            $this -> ping -> ping();
        }
    }

    protected function onConnect(Packet $data = null)
    {
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

    public function onClose($server, $frame)
    {}

    public function onMessage(Server $server, $frame)
    {
        $data = $this->packet::fromString($frame -> data);
        $this -> ping -> clearPingTimeOut();
        match (intval($data -> type)) {
            Packet::MESSAGE => $this->Parser($data -> data),
            // response
            Packet::PING => $server -> push($this->fd, Packet::PONG),
            Packet::PONG => $this->ping -> ping(),
            default => $server -> close($this->fd)
        };
    }

    protected function Parser($data)
    {
        $this->websocket -> fd = $this -> fd;
        $data = Packet::decode($data);
        $this->websocket -> nsp = $data -> nsp;

        $event = match (intval($data -> type)) {
            Packet::CONNECT => $this -> onConnect($data),
            Packet::EVENT => function () use ($data) {
                $this->websocket ->services -> callConfigHandle($this -> config['Handle'], [$this->websocket, [
                    'event' => $data -> data [0],
                    'data' => isset($data -> data[1]) ? (
                        json_decode($data -> data[1], JSON_UNESCAPED_UNICODE) ?: $data -> data[1]
                    ) : ''
                ]]);
            },
            default => $this->server -> close($this->fd)
        };
        return is_callable($event) ? call_user_func($event) : true;
    }
}