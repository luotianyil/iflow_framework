<?php

namespace iflow\swoole;

use iflow\console\lib\Command;
use iflow\Container\Container;
use iflow\Container\implement\annotation\tools\data\Inject;

class ServicesCommand extends Command {

    #[Inject]
    public Config $config;

    protected string $baseClass = '\\iflow\\swoole\\implement\\';

    public function handle(array $event = []): mixed {
        // TODO: Implement handle() method.

        $this->config -> initConfigs($event);
        $service = $this->getServiceClass();

        if ($service === '') {
            $this->Console -> outWrite('swoole service d\'not'. PHP_EOL);
            return $service;
        }

        $services = Container::getInstance() -> make($this->getServiceClass(), [ $this ]);
        $command = $event[0] ?? 'start';

        $this->config -> setServicesAbstract($services);
        return Container::getInstance() -> invoke([ $services, $command ], [ $this ]);
    }


    public function setServices() {
       Container::getInstance() -> register(
           \Swoole\Server::class,
           $this->config -> getServicesAbstract() -> getSwService()
       );
    }


    /**
     * @return Config
     */
    public function getConfig(): Config {
        return $this->config;
    }


    public function callConfHandle(string $hClass, array $params = []): mixed {
        // TODO: 配置类回调
        if (!class_exists($hClass)) return [];
        $hObject = Container::getInstance() -> make($hClass, isNew: true);
        return $hObject -> handle(...$params);
    }

    /**
     * 启动服务类型
     * @return bool
     */
    public function isStartServer(): bool {
        $event = $this->config -> getCommandEvent();
        if (count($event) <= 2) return true;

        return $event[2] !== 'client';
    }

    protected function getServiceClass(): string {
        $isClient = $this->isStartServer() ? 'Server' : 'Client';
        $event = $this->config -> getCommandEvent();

        $service = $event[1] ?? 'service';

        $service = $service === 'service' ? 'http' : $service;

        $class = sprintf("{$this -> baseClass}$isClient\%s\Service", ucwords($service));

        if (class_exists($class)) return $class;

        return "";
    }
}