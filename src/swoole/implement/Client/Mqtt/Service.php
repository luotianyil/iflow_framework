<?php

namespace iflow\swoole\implement\Client\Mqtt;

use iflow\Container\implement\annotation\tools\data\Inject;
use iflow\swoole\abstracts\ServicesAbstract;
use Simps\MQTT\Client;
use Swoole\Coroutine\Scheduler;

class Service extends ServicesAbstract {

    #[Inject]
    public Scheduler $scheduler;

    protected MqttClient $mqttClient;

    public function start() {
        $this -> scheduler -> add(function () {
            $this -> mqttClient = new MqttClient($this -> servicesCommand -> config);
            $this -> mqttClient -> start();


            $this -> SwService = $this
                -> setPid($this->config -> get('swConfig@pid_file'))
                -> mqttClient
                -> getClient();
            $this -> servicesCommand -> setServices();
            $this->printStartContextToConsole('mqtt');

            $this -> mqttClient -> wait();
        });
        $this->scheduler -> start();
    }

    /**
     * @return MqttClient
     */
    public function getMqttClient(): MqttClient {
        return $this->mqttClient;
    }

    protected function getSwooleServiceClass(): string {
        return Client::class;
    }

}
