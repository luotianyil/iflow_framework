<?php


namespace iflow\Swoole\Udp;

use iflow\Container\implement\generate\exceptions\InvokeClassException;
use iflow\Swoole\Udp\lib\udpService;

class Services extends \iflow\Swoole\Services
{
    protected array $initializers = [ udpService::class ];

    /**
     * 启动UDP服务
     * @throws InvokeClassException
     */
    public function run() {
        if ($this->isStartServer()) {
            $this->initializer();
        }
    }
}