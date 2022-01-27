<?php


namespace iflow\Swoole\Tcp;


use iflow\Container\implement\generate\exceptions\InvokeClassException;
use iflow\Swoole\Tcp\lib\tcpService;

class Services extends \iflow\Swoole\Services
{

    protected array $initializers = [
        tcpService::class
    ];

    /**
     * 启动TCP服务
     * @throws InvokeClassException
     */
    public function run() {
        if ($this->isStartServer()) {
            $this->initializer();
        }
    }

}