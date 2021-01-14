<?php


namespace iflow\Swoole\Services\WebSocket\socketio;


use iflow\Swoole\Services\WebSocket\webSocket;

class Event
{
    protected array $config = [];
    protected webSocket $websocket;
    public Parser $parser;

    public function __construct(webSocket $websocket)
    {
        $this->websocket = $websocket;
        $this->config = $websocket -> services -> config;
        $this->parser = new Parser();
    }

    public function onOpen($server, $frame)
    {
        if (!empty($frame->get['sid'])) {
            $payload        = json_encode(
                [
                    'sid'          => base64_encode(uniqid()),
                    'upgrades'     => [],
                    'pingInterval' => $this->config['websocket']['ping_interval'],
                    'pingTimeout'  => $this->config['websocket']['ping_timeout'],
                ]
            );
            $initPayload    = Parser::OPEN . $payload;
            $connectPayload = Parser::MESSAGE . Parser::CONNECT;

            $server->push($frame -> fd, $initPayload);
            $server->push($frame -> fd, $connectPayload);
        }
    }

    public function onClose($server, $frame)
    {}

    public function onMessage($server, $frame)
    {
        $data = $this->parser::getPayload($frame -> data);
        if ($data) {
            $this->websocket -> fd = $frame -> fd;
            $this->websocket ->services -> callConfigHandle($this -> config['Handle'], [$this->websocket, $data['event'], $data['data']]);
        } else {
            $this->parser::heartbeat($server, $frame -> fd, $frame -> data);
        }
    }
}