<?php

namespace iflow\swoole\implement\Server\Mqtt\Events\Traits;

use iflow\Container\implement\generate\exceptions\InvokeClassException;
use iflow\swoole\Config;
use iflow\swoole\implement\Server\Mqtt\Subscribe\Subscribe;
use Swoole\Server;

trait MQTTServerHelperTrait {

    /**
     * 构建投递任务 配置类参数
     * @param array $vars
     * @return array
     */
    protected function getTaskConfigArgs(array $vars): array {
        return [
            'value' => Config::class,
            'args' => [
                [ ...$this->servicesCommand -> config -> toArray(), ...$vars ]
            ],
            'isNew' => true,
            'type' => 'object'
        ];
    }

    /**
     * 获取指定账户连接状态
     * @param Server $server
     * @param int $fd
     * @param string $username
     * @return array
     * @throws \RedisException
     * @throws InvokeClassException
     */
    public function getUsernameConnectionStatus(Server $server, int $fd, string $username): array {
        if (!$username) return [ 'fd' => 0, '_exists' => false ];
        $_fd = app(Subscribe::class) -> getFdByUsername($username);
        return [ 'fd' => $_fd, '_exists' => $_fd !== $fd && $server -> exists($_fd) ];
    }

}