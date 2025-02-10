<?php

namespace iflow\swoole\implement\Server\Mqtt\Packet\abstracts;

use iflow\swoole\Config;
use iflow\swoole\implement\Server\Mqtt\Packet\MQTT;
use iflow\swoole\implement\Server\Mqtt\Packet\Parser;
use iflow\swoole\implement\Server\Mqtt\Packet\ReceiveInterface;
use iflow\swoole\implement\Server\Mqtt\Subscribe\Subscribe;
use Simps\MQTT\Hex\ReasonCode;
use Simps\MQTT\Protocol\Types;
use Swoole\Server;

abstract class ReceiveAbstract implements ReceiveInterface {

    protected MQTT $MQTTPacket;

    public function __construct() {
        $this->MQTTPacket = new MQTT(new Parser());
    }

    /**
     * 权限校验
     * @param array $data
     * @param Config $config
     * @return int
     */
    protected function checkAuth(array $data, Config $config): int {
        $auth = $config -> get('subscribe@auth');
        if (!$auth) return ReasonCode::SUCCESS;

        $auth = valid_closure($auth, [ $data ]);
        return $auth($data, $config);
    }

    protected function getProtocolLevel(Config $config): int {
        return $config -> get(
            'mqttEvent@protocol_level', $config -> get('protocol_level', 5)
        );
    }

    protected function publishByTopic(
        Server $server, string $topic, array $data, Config $config, int $fd = 0
    ): void {
        $topicAllFd = app(Subscribe::class) -> getSubscribeTopicAllFd($topic);
        if (($rIndex = array_search($fd, $topicAllFd)) !== false) unset($topicAllFd[$rIndex]);

        array_map(function (int $_fd) use ($server, $topic, $data, $config, $fd) {
            $server -> send(
                $_fd,
                $this -> MQTTPacket
                    -> setMessageId($data['message_id'] ?? 0)
                    -> setMessage($data['message'] ?? '')
                    -> setTopic($topic)
                    -> setType(Types::PUBLISH)
                    -> setCode(ReasonCode::SUCCESS)
                    -> setQos($data['qos'] ?? ReasonCode::GRANTED_QOS_0)
                    -> setProperties(
                        $config -> get('MQTTOptions@properties')
                    )
                    -> pack($this -> getProtocolLevel($config))
            );
        }, $topicAllFd);

    }


    /**
     * 获取当前消息的所有主题名称
     * @param array $topics
     * @return array
     */
    protected function getTopicNameByPublish(array $topics): array {
        $topicNames = [];
        foreach ($topics as $topic => $topicNameOrOptions) {
            if (is_integer($topic)) {
                $topicNames[] = $topicNameOrOptions;
                continue;
            }
            $topicNames[] = $topic;
        }
        return $topicNames;
    }

}
