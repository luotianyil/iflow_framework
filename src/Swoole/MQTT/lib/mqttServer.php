<?php


namespace iflow\Swoole\MQTT\lib;


use iflow\Swoole\Mqtt\Services;

class mqttServer
{

    protected array $events = [
        'receive' => 'onReceive',
        'connect' => 'onConnect',
        'close' => 'onClose',
    ];

    public function initializer(Services $services)
    {
        $services -> eventInit(new mqttServerEvent(new Parser(), $services), $this->events);
    }
}