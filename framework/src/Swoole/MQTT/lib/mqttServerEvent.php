<?php


namespace iflow\Swoole\MQTT\lib;


use iflow\Swoole\Event;
use iflow\Swoole\MQTT\Services;

class mqttServerEvent
{
    use Event;

    protected Parser $parser;
    protected Services $services;

    public function __construct(Parser $parser, Services $services)
    {
        $this->parser = $parser;
        $this->services = $services;
    }

    public function onReceive($server, $fd, $from_id, $data)
    {
        $header = $this->parser -> getHeader($data);
        $msg = '';
        if ($header['type'] == 1) {
            $resp = chr(32) . chr(2) . chr(0) . chr(0);
            $msg = $this->parser -> eventConnect($header, substr($data, 2));
            $server->send($fd, $resp);
        } elseif ($header['type'] == 3) {
            $offset = 2;
            $topic = $this->parser-> decodeString(substr($data, $offset));
            $offset += strlen($topic) + 2;
            $msg = substr($data, $offset);
        }

        $this->services -> callConfigHandle($this->services->config['Handle'], [$server, $fd, $from_id, $data, [
            'header' => $header,
            'message' => $msg
        ]]);
    }

    public function onConnect($server, $fd)
    {
        $this->services -> callConfigHandle($this->services->config['mqttEvent']['connectAfter'], [$server, $fd]);
    }

    public function onClose($server, $fd)
    {}
}
