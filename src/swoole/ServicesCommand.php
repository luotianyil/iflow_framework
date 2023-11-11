<?php

namespace iflow\swoole;

use iflow\console\Adapter\Command;
use iflow\Container\Container;
use iflow\Container\implement\annotation\tools\data\Inject;
use iflow\swoole\abstracts\ServicesAbstract;
use Swoole\Server;

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
           Server::class,
           $this->config -> getServicesAbstract() -> getSwService()
       );

       Container::getInstance() -> register(ServicesCommand::class, $this);
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

    /**
     * @return ServicesAbstract::class
     */
    protected function getServiceClass(): string {
        $event = $this->config -> getCommandEvent();

        $ServiceType = $this->getServiceType();

        $service = $event[1] ?? 'service';
        $service = $service === 'service' ? 'http' : $service;

        $class = sprintf("{$this -> baseClass}{$ServiceType}\%s\Service", ucwords($service));
        if (class_exists($class)) return $class;

        return "";
    }


    /**
     * 获取服务类型
     * @return string
     */
    protected function getServiceType(): string {
        $isClient = $this->isStartServer();
        $event = $this->config -> getCommandEvent();
        if (count($event) <= 2) return $isClient ? 'Server' : 'Client';

        return $event[2] !== 'Server' ? ucwords($event[2]) : 'Server';
    }
}