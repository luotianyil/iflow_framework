<?php

namespace iflow\swoole\implement\Server\Mqtt\Packet\abstracts;

use iflow\swoole\implement\Server\Mqtt\Packet\MQTT;
use iflow\swoole\implement\Server\Mqtt\Packet\Parser;
use Simps\MQTT\Protocol\Types;
use Swoole\Server;

abstract class ReceiveAbstract {

    // MQTT 连接初始化
    abstract public function onMqConnect(Server $server, array $data, int $fd): bool;

    // MQTT PING
    public function onMqPingReq(Server $server, array $data, int $fd): bool {
        $mqtt = new MQTT(new Parser());
        $message = $mqtt
            -> setType(Types::PINGRESP)
            -> setCode(0)
            -> setSessionPresent(0)
            -> pack();
        return $server -> send($fd, $message);
    }

    // MQTT 断开连接
    abstract public function onMqDisconnect(Server $server, array $data, int $fd): bool;

    // MQTT 发布主题
    abstract public function onMqPublish(Server $server, array $data, int $fd): bool;

    // MQTT 发布订阅
    abstract public function onMqSubscribe(Server $server, array $data, int $fd): bool;

    // MQTT 取消订阅
    abstract public function onMqUnsubscribe(Server $server, array $data, int $fd): bool;

}