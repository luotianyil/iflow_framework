<?php


namespace iflow\Swoole\MQTT\lib\receiveCallbacks;


use Swoole\Server;

abstract class receiveCallback
{

    // MQTT连接初始化
    abstract public function onMqConnect(Server $server, array $data, int $fd);

    // MQTT PING
    abstract public function onMqPingreq(Server $server, array $data, int $fd);

    // MQTT 断开连接
    abstract public function onMqDisconnect(Server $server, array $data, int $fd);

    // MQTT 发布主题
    abstract public function onMqPublish(Server $server, array $data, int $fd);

    // MQTT 发布订阅
    abstract public function onMqSubscribe(Server $server, array $data, int $fd);

    // MQTT 取消订阅
    abstract public function onMqUnsubscribe(Server $server, array $data, int $fd);
}