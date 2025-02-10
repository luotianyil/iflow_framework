<?php

namespace iflow\swoole\implement\Server\Mqtt\Subscribe\Traits;

use iflow\Utils\Tools\Timer;
use Swoole\Process;
use Swoole\Server;

trait SubscribeTTLTrait {

    public function ttlUnSubscribe(): void {
        app(Server::class) -> addProcess(new Process(function () {
            Timer::tick(($this -> subscribeConfig['ttl'] ?: 30) * 1000, function () {
                // TODO: 定时刷新连接状态
            });
        }));
    }

}