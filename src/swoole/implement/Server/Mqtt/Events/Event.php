<?php

namespace iflow\swoole\implement\Server\Mqtt\Events;

use iflow\swoole\Config;
use iflow\swoole\implement\Server\Mqtt\Packet\Parser;
use iflow\swoole\ServicesCommand;
use Simps\MQTT\Protocol\Types;
use Swoole\Server;

class Event {

    protected int $protocol_level = 5;

    protected array $MQEvent = [
        // MQTT连接初始化
        Types::CONNECT => 'onMqConnect',
        // MQTT PING
        Types::PINGREQ => 'onMqPingreq',
        // MQTT 断开连接
        Types::DISCONNECT => 'onMqDisconnect',
        // MQTT 发布消息
        Types::PUBLISH => 'onMqPublish',
        // MQTT 发布订阅
        Types::SUBSCRIBE => 'onMqSubscribe',
        // MQTT 取消订阅
        Types::UNSUBSCRIBE => 'onMqUnsubscribe'
    ];

    public function __construct(
        protected Parser $parser,
        protected ServicesCommand $servicesCommand
    ) {
        $this->protocol_level = $this->servicesCommand -> config -> get('mqttEvent@protocol_level', 5);
    }

    public function onReceive(Server $server, $fd, $from_id, $data): bool {
        $packet = $this->parser -> unpack($data, $this->protocol_level);

        // 非 MQTT协议 关闭连接
        if (isset($packet['protocol_name']) && $packet['protocol_name'] !== 'MQTT') {
            $server->close($fd);
            return false;
        }

        // 获取 客户端传来的 MQTT通讯协议 5 OR 3
        if (isset($packet['protocol_level']) && is_numeric($packet['protocol_level'])) {
            $this->protocol_level = $packet['protocol_level'];
        }

        $handleClass = $this -> servicesCommand -> config -> get('messageType', MQTTEvent::class);
        $method = $this->MQEvent[$packet['type']] ?? 'onMessage';

        $server -> task([
            'callable' => [ $handleClass, $method ],
            'callable_params' => [
                [
                    'value' => $server::class,
                    'type' => 'object'
                ],
                $packet, $fd,
                [
                    'value' => Config::class,
                    'args' => $this->servicesCommand -> config -> toArray(),
                    'isNew' => true,
                    'type' => 'object',
                ]
            ]
        ]);
        return false;
    }

    public function onConnect(Server $server, $fd): void {
        $this->servicesCommand -> callConfHandle(
            $this->servicesCommand -> config -> get('mqttEvent@connectAfter', ''),
            [ $server, $fd ]
        );
    }

    public function onOpen($server, $req): void {
        // TODO: Implement onOpen() method.
    }

    public function onMessage($server, $req): void {
        // TODO: Implement onMessage() method.
    }

    public function onConnection($server, $fd): void {
        // TODO: Implement onConnection() method.
    }


    public function onClose(): void {
    }

    /**
     * @return int
     */
    public function getProtocolLevel(): int {
        return $this->protocol_level;
    }

}