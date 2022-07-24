<?php

namespace iflow\swoole\implement\Server\Mqtt;

use iflow\Container\Container;
use iflow\swoole\abstracts\ServicesAbstract;
use iflow\swoole\implement\Server\Mqtt\Events\Event;
use iflow\swoole\implement\Server\Mqtt\Packet\Parser;

class Service extends ServicesAbstract {

    protected array $events = [
        'receive' => 'onReceive',
        'connect' => 'onConnect',
        'close' => 'onClose'
    ];

    public function start() {
        parent::start(); // TODO: Change the autogenerated stub

        $this->registerSwServiceEvent(
            Container::getInstance() -> make(
                $this->getEventClass(), [ new Parser(), $this -> servicesCommand ]
            )
        ) -> printStartContextToConsole('mqtt');

        $this->SwService -> start();
    }


    protected function getEventClass(): string {
        $class = parent::getEventClass(); // TODO: Change the autogenerated stub
        return $class ?: Event::class;
    }

}