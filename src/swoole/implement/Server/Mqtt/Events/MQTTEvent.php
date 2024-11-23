<?php

namespace iflow\swoole\implement\Server\Mqtt\Events;

use iflow\swoole\Config;
use iflow\swoole\implement\Server\Mqtt\Packet\abstracts\ReceiveAbstract;
use Simps\MQTT\Protocol\Types;
use Swoole\Server;

class MQTTEvent extends ReceiveAbstract {

    /**
     * MQTT 连接初始化
     * @param Server $server
     * @param array $data
     * @param int $fd
     * @param Config $config
     * @return bool
     */
    public function onMqConnect(Server $server, array $data, int $fd, Config $config): bool {
        return $server -> send(
            $fd,
            $this->MQTTPacket
                -> setMessageId($data['message_id'] ?? 0)
                -> setType(Types::CONNACK)
                -> setCode(0)
                -> setSessionPresent(0)
                -> setProperties(
                    $config -> get('MQTTOptions@properties')
                )
                -> pack()
        );
    }

    /**
     * MQTT PING
     * @param Server $server
     * @param array $data
     * @param int $fd
     * @param Config $config
     * @return bool
     */
    public function onMqPingReq(Server $server, array $data, int $fd, Config $config): bool {
        $message = $this->MQTTPacket
            -> setMessageId($data['message_id'] ?? 0)
            -> setType(Types::PINGRESP)
            -> setCode(0)
            -> pack();
        return $server -> send($fd, $message);
    }

    /**
     * MQTT 断开连接
     * @param Server $server
     * @param array $data
     * @param int $fd
     * @param Config $config
     * @return bool
     */
    public function onMqDisconnect(Server $server, array $data, int $fd, Config $config): bool {
        $message = $this->MQTTPacket
            -> setMessageId($data['message_id'] ?? 0)
            -> setType(Types::DISCONNECT)
            -> setCode(0)
            -> pack();
        return $server -> send($fd, $message);
    }

    /**
     * MQTT 发布主题
     * @param Server $server
     * @param array $data
     * @param int $fd
     * @param Config $config
     * @return bool
     */
    public function onMqPublish(Server $server, array $data, int $fd, Config $config): bool {
        $message = $this->MQTTPacket
            -> setMessageId($data['message_id'] ?? 0)
            -> setType(Types::PUBACK)
            -> setCode(0)
            -> pack();
        return $server -> send($fd, $message);
    }

    /**
     * MQTT 发布订阅
     * @param Server $server
     * @param array $data
     * @param int $fd
     * @param Config $config
     * @return bool
     */
    public function onMqSubscribe(Server $server, array $data, int $fd, Config $config): bool {
        $message = $this->MQTTPacket
            -> setMessageId($data['message_id'] ?? 0)
            -> setType(Types::SUBACK)
            -> setCode(0)
            -> pack();
        return $server -> send($fd, $message);
    }

    /**
     * MQTT 取消订阅
     * @param Server $server
     * @param array $data
     * @param int $fd
     * @param Config $config
     * @return bool
     */
    public function onMqUnsubscribe(Server $server, array $data, int $fd, Config $config): bool {
        $message = $this->MQTTPacket
            -> setMessageId($data['message_id'] ?? 0)
            -> setType(Types::UNSUBACK)
            -> setCode(0)
            -> pack();
        return $server -> send($fd, $message);
    }
}