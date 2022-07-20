<?php

namespace iflow\swoole;

use iflow\Container\Container;
use iflow\swoole\abstracts\ServicesAbstract;
use iflow\swoole\Exceptions\ConfigException;
use iflow\Utils\ArrayTools;

class Config extends ArrayTools {

    protected array $commandEvent = [];

    protected string $swooleConfigKey;

    protected array $Services = [
        'server' => [],
        'client' => [],
        'service' => []
    ];

    protected ServicesAbstract $servicesAbstract;

    public function initConfigs(array $event): Config {
        $this->commandEvent = $event;

        if (count($this->commandEvent) === 1) $this->swooleConfigKey = 'swoole.service';

        $this->getSwooleConfigKey();

        $this->items = config($this->swooleConfigKey, []);

        if (empty($this->items['swConfig'])) {
            throw new ConfigException('Swoole Config is Empty !!!');
        }
        return $this;
    }

    protected function getSwooleConfigKey(): void {
        $event = array_slice($this->commandEvent, 1, count($this->commandEvent));
        if (count($event) === 1) $this->swooleConfigKey = 'swoole.service';
        else $this->swooleConfigKey = 'swoole.'. implode('@', $event);
    }


    /**
     * @param ServicesAbstract $servicesAbstract
     * @throws \Exception
     */
    public function setServicesAbstract(ServicesAbstract $servicesAbstract): void {
        $this->servicesAbstract = $servicesAbstract;
        Container::getInstance() -> register($servicesAbstract::class, $servicesAbstract);
    }


    /**
     * @return ServicesAbstract
     */
    public function getServicesAbstract(): ServicesAbstract {
        return $this->servicesAbstract;
    }


    /**
     * @return array
     */
    public function getCommandEvent(): array {
        return $this->commandEvent;
    }
}