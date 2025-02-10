<?php

namespace iflow\swoole\implement\Server\Mqtt\Packet;

use iflow\swoole\Config;
use Swoole\Server;

interface ReceiveInterface {

    /**
     * MQTT 连接初始化
     * @param Server $server
     * @param array $data
     * @param int $fd
     * @param Config $config
     * @return bool
     */
    public function onMqConnect(Server $server, array $data, int $fd, Config $config): bool;

    /**
     * MQTT 接收信息
     * @param Server $server
     * @param array $data
     * @param int $fd
     * @param Config $config MQTT 服务配置
     * @return bool
     */
    public function onMessage(Server $server, array $data, int $fd, Config $config): bool;

    /**
     * MQTT PING
     * @param Server $server
     * @param array $data
     * @param int $fd
     * @param Config $config
     * @return bool
     */
    public function onMqPingReq(Server $server, array $data, int $fd, Config $config): bool;

    /**
     * MQTT 断开连接
     * @param Server $server
     * @param array $data
     * @param int $fd
     * @param Config $config
     * @return bool
     */
    public function onMqDisconnect(Server $server, array $data, int $fd, Config $config): bool;

    /**
     * MQTT 发布主题
     * @param Server $server
     * @param array $data
     * @param int $fd
     * @param Config $config
     * @return bool
     */
    public function onMqPublish(Server $server, array $data, int $fd, Config $config): bool;

    /**
     * MQTT 发布订阅
     * @param Server $server
     * @param array $data
     * @param int $fd
     * @param Config $config
     * @return bool
     */
    public function onMqSubscribe(Server $server, array $data, int $fd, Config $config): bool;

    /**
     * MQTT 取消订阅
     * @param Server $server
     * @param array $data
     * @param int $fd
     * @param Config $config
     * @return bool
     */
    public function onMqUnsubscribe(Server $server, array $data, int $fd, Config $config): bool;

}