<?php


namespace iflow\Swoole\MQTT\lib;


use iflow\Swoole\Mqtt\Services;

class mqttServer
{

    protected array $events = [
        'receive' => 'onReceive',
        'connect' => 'onConnect'
    ];

    protected Parser $parser;

    public function initializer(Services $services)
    {
        $services -> eventInit($this, $this->events);
        $this->parser = new Parser();
    }

    public function onReceive($server, $fd, $from_id, $data)
    {
        var_dump($this->parser -> decoding($data));
    }

    public function onConnect($server, $fd)
    {
        var_dump($fd);
    }
}