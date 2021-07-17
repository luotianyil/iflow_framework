<?php


namespace iflow\Swoole\MQTT;

use iflow\Swoole\MQTT\lib\mqttClientCommand;
use iflow\Swoole\MQTT\lib\mqttServer;

class Services extends \iflow\Swoole\Services
{

    protected array $initializers = [
        mqttServer::class
    ];

    public function run()
    {
        $this->userEvent[2] = empty($this->userEvent[2]) ? 'client' : ($this->userEvent[2] === 'server' ? 'server' : 'client');
        if ($this->userEvent[2] === 'client') {
            call_user_func([new mqttClientCommand(), 'initializer'], $this);
        } else $this->initializer();
    }

}