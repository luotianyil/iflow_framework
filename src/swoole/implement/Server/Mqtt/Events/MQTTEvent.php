<?php

namespace iflow\swoole\implement\Server\Mqtt\Events;

use iflow\Container\implement\generate\exceptions\InvokeClassException;
use iflow\swoole\Config;
use iflow\swoole\implement\Server\Mqtt\Packet\abstracts\ReceiveAbstract;
use iflow\swoole\implement\Server\Mqtt\Subscribe\Subscribe;
use Simps\MQTT\Hex\ReasonCode;
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
     * @throws \Exception
     */
    public function onMqConnect(Server $server, array $data, int $fd, Config $config): bool {
        $clientInfo = $config -> get('clientInfo', []);

        $checkAuth = $this -> checkAuth([
            'pack' => $data, 'client_info' => $clientInfo, '_exists' => $config -> get('_exists')
        ], $config);

        $messageAck = $this->MQTTPacket
            -> setMessageId($data['message_id'] ?? 0)
            -> setType(Types::CONNACK)
            -> setCode($checkAuth)
            -> setSessionPresent(0)
            -> setProperties(
                $config -> get('MQTTOptions@properties')
            );

        if ($checkAuth === ReasonCode::SUCCESS) {
            if ($config -> get('_exists@_exists')) $server -> send(
                $config -> get('_exists@fd'),
                (clone $messageAck) -> setCode(ReasonCode::SESSION_TAKEN_OVER) -> pack($this -> getProtocolLevel($config))
            );
            app(Subscribe::class) -> setClientInfoByFd($fd, [ ...$clientInfo, 'username' => $data['user_name'] ?? '' ]);
        }

        return $server -> send($fd, $messageAck -> pack($this -> getProtocolLevel($config)));
    }

    public function onMessage(Server $server, array $data, int $fd, Config $config): bool {
        // TODO: Implement onMessage() method.
        return true;
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
            -> setCode(ReasonCode::SUCCESS)
            -> pack($this -> getProtocolLevel($config));

        return $server -> send($fd, $message);
    }

    /**
     * MQTT 断开连接
     * @param Server $server
     * @param array $data
     * @param int $fd
     * @param Config $config
     * @return bool
     * @throws \Exception
     */
    public function onMqDisconnect(Server $server, array $data, int $fd, Config $config): bool {
        app(Subscribe::class) -> clearConnectByFd($fd);
        $message = $this->MQTTPacket
            -> setMessageId($data['message_id'] ?? 0)
            -> setType(Types::DISCONNECT)
            -> setCode(0)
            -> pack($this -> getProtocolLevel($config));
        return $server -> send($fd, $message);
    }

    /**
     * MQTT 发布消息
     * @param Server $server
     * @param array $data
     * @param int $fd
     * @param Config $config
     * @return bool
     */
    public function onMqPublish(Server $server, array $data, int $fd, Config $config): bool {
        if ($data['qos'] === ReasonCode::GRANTED_QOS_1) {
            $server -> send($fd, $this->MQTTPacket
                -> setMessageId($data['message_id'] ?? 0)
                -> setTopic($data['topic'] ?? '')
                -> setMessage($data['message'] ?? '')
                -> setType(Types::PUBACK)
                -> setCode(ReasonCode::SUCCESS)
                -> pack($this -> getProtocolLevel($config)));
        }
        $this -> publishByTopic($server, $data['topic'], $data, $config, $fd);
        return true;
    }

    /**
     * MQTT 发布订阅
     * @param Server $server
     * @param array $data
     * @param int $fd
     * @param Config $config
     * @return bool
     * @throws InvokeClassException
     */
    public function onMqSubscribe(Server $server, array $data, int $fd, Config $config): bool {
        app(Subscribe::class) -> subscribe($this -> getTopicNameByPublish($data['topics']), $fd);

        return $server -> send(
            $fd, $this->MQTTPacket
            -> setMessageId($data['message_id'] ?? 0)
            -> setType(Types::SUBACK)
            -> setCodes([ ReasonCode::SUCCESS ])
            -> setProperties(
                $config -> get('MQTTOptions@properties')
            )
            -> pack($this -> getProtocolLevel($config))
        );
    }

    /**
     * MQTT 取消订阅
     * @param Server $server
     * @param array $data
     * @param int $fd
     * @param Config $config
     * @return bool
     * @throws InvokeClassException
     */
    public function onMqUnsubscribe(Server $server, array $data, int $fd, Config $config): bool {
        app(Subscribe::class) -> unSubscribe($this -> getTopicNameByPublish($data['topics']), $fd);
        $message = $this->MQTTPacket
            -> setMessageId($data['message_id'] ?? 0)
            -> setType(Types::UNSUBACK)
            -> setCodes([ ReasonCode::SUCCESS ])
            -> pack($this -> getProtocolLevel($config));
        return $server -> send($fd, $message);
    }
}