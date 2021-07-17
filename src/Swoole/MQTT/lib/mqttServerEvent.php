<?php


namespace iflow\Swoole\MQTT\lib;

use iflow\Swoole\Event;
use iflow\Swoole\MQTT\lib\receiveCallbacks\receiveCallback;
use iflow\Swoole\MQTT\Services;
use Simps\MQTT\Protocol\Types;

class mqttServerEvent extends Event
{

    protected Parser $parser;
    protected Services $services;

    protected int $protocol_level = 5;
    protected array $handleEvent = [
        // MQTT连接初始化
        Types::CONNECT => 'onMqConnect',
        // MQTT PING
        Types::PINGREQ => 'onMqPingreq',
        // MQTT 断开连接
        Types::DISCONNECT => 'onMqDisconnect',
        // MQTT 发布主题
        Types::PUBLISH => 'onMqPublish',
        // MQTT 发布订阅
        Types::SUBSCRIBE => 'onMqSubscribe',
        // MQTT 取消订阅
        Types::UNSUBSCRIBE => 'onMqUnsubscribe'
    ];

    public function __construct(Parser $parser, Services $services)
    {
        $this->parser = $parser;
        $this->services = $services;
        $this->protocol_level = $this->services -> config['mqttEvent']['protocol_level'] ?? 5;
    }

    public function onReceive($server, $fd, $from_id, $data): bool
    {
        $packet = $this->parser -> unpack($data, $this->protocol_level);

        // 非 MQTT协议 关闭连接
        if (isset($packet['protocol_name']) && $packet['protocol_name'] != "MQTT") {
            $server->close($fd);
            return false;
        }

        $handleClass = $this -> services -> config['messageType'] ?? '';

        if (class_exists($handleClass)) {
            $handle = new $handleClass();
            // 验证并执行方法回调
            if ($handle instanceof receiveCallback) {
                $action = $this->handleEvent[$packet['type']] ?? '';
                return method_exists($handle, $action) && call_user_func([$handle, $action], ...[
                    $server, $packet, $fd
                ]);
            }
        }
        return false;
    }

    public function onConnect($server, $fd)
    {
        $this->services -> callConfigHandle(
            $this->services->config['mqttEvent']['connectAfter'], [$server, $fd]
        );
    }

    public function onOpen($server, $req)
    {
        // TODO: Implement onOpen() method.
    }

    public function onMessage($server, $req)
    {
        // TODO: Implement onMessage() method.
    }

    public function onConnection($server, $fd)
    {
        // TODO: Implement onConnection() method.
    }

    /**
     * @return int
     */
    public function getProtocolLevel(): int
    {
        return $this->protocol_level;
    }
}
