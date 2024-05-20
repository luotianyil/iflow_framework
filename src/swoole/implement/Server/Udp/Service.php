<?php

namespace iflow\swoole\implement\Server\Udp;

use iflow\Container\Container;
use iflow\swoole\abstracts\ServicesAbstract;
use iflow\swoole\implement\Server\Udp\Events\Event;

class Service extends ServicesAbstract {

    protected string $defaultEventClass = Event::class;

    protected array $events = [
        'packet' => 'onPacket'
    ];

    public function start() {
        parent::start(); // TODO: Change the autogenerated stub
        $this -> registerSwServiceEvent(
            Container::getInstance() -> make($this->getEventClass(), [ $this ])
        ) -> printStartContextToConsole('udp');
        $this->SwService -> start();
    }

}