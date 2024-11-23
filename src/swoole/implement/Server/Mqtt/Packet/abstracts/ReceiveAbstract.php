<?php

namespace iflow\swoole\implement\Server\Mqtt\Packet\abstracts;

use iflow\swoole\Config;
use iflow\swoole\implement\Server\Mqtt\Packet\MQTT;
use iflow\swoole\implement\Server\Mqtt\Packet\Parser;
use Swoole\Server;

abstract class ReceiveAbstract {

    protected MQTT $MQTTPacket;

    public function __construct() {
        $this->MQTTPacket = new MQTT(new Parser());
    }

    // MQTT 连接初始化
    abstract public function onMqConnect(Server $server, array $data, int $fd, Config $config): bool;

    // MQTT PING
    abstract public function onMqPingReq(Server $server, array $data, int $fd, Config $config): bool;

    // MQTT 断开连接
    abstract public function onMqDisconnect(Server $server, array $data, int $fd, Config $config): bool;

    // MQTT 发布主题
    abstract public function onMqPublish(Server $server, array $data, int $fd, Config $config): bool;

    // MQTT 发布订阅
    abstract public function onMqSubscribe(Server $server, array $data, int $fd, Config $config): bool;

    // MQTT 取消订阅
    abstract public function onMqUnsubscribe(Server $server, array $data, int $fd, Config $config): bool;

}
