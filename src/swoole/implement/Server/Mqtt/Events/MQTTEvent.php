<?php

namespace iflow\swoole\implement\Server\Mqtt\Events;

use iflow\swoole\Config;
use iflow\swoole\implement\Server\Mqtt\Packet\abstracts\ReceiveAbstract;
use Simps\MQTT\Protocol\Types;
use Swoole\Server;

class MQTTEvent extends ReceiveAbstract {

    // MQTT 连接初始化
    public function onMqConnect(Server $server, array $data, int $fd, Config $config): bool {
        return (bool)$server -> send(
            $fd,
            $this->MQTTPacket
                -> setType(Types::CONNACK)
                -> setCode(0)
                -> setSessionPresent(0)
                -> setProperties(
                    $config -> get('MQTTOptions@properties')
                )
                -> pack()
        );
    }

    // MQTT PING
    public function onMqPingReq(Server $server, array $data, int $fd, Config $config): bool {
        $message = $this->MQTTPacket
            -> setType(Types::PINGRESP)
            -> setCode(0)
            -> pack();
        return $server -> send($fd, $message);
    }

    // MQTT 断开连接
    public function onMqDisconnect(Server $server, array $data, int $fd, Config $config): bool {
        $message = $this->MQTTPacket
            -> setType(Types::DISCONNECT)
            -> setCode(0)
            -> pack();
        return $server -> send($fd, $message);
    }

    // MQTT 发布主题
    public function onMqPublish(Server $server, array $data, int $fd, Config $config): bool {
        $message = $this->MQTTPacket
            -> setType(Types::PUBACK)
            -> setCode(0)
            -> pack();
        return $server -> send($fd, $message);
    }

    // MQTT 发布订阅
    public function onMqSubscribe(Server $server, array $data, int $fd, Config $config): bool {
        $message = $this->MQTTPacket
            -> setType(Types::SUBACK)
            -> setCode(0)
            -> pack();
        return $server -> send($fd, $message);
    }

    // MQTT 取消订阅
    public function onMqUnsubscribe(Server $server, array $data, int $fd, Config $config): bool {
        $message = $this->MQTTPacket
            -> setType(Types::UNSUBACK)
            -> setCode(0)
            -> pack();
        return $server -> send($fd, $message);
    }
}