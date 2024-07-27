<?php

namespace iflow\swoole\implement\Client\Mqtt;

use iflow\Container\Container;
use iflow\Container\implement\annotation\exceptions\AttributeTypeException;
use iflow\Container\implement\generate\exceptions\InvokeClassException;
use iflow\Container\implement\generate\exceptions\InvokeFunctionException;
use iflow\swoole\Config;
use Simps\MQTT\Client;
use Simps\MQTT\Config\ClientConfig;
use Simps\MQTT\Hex\ReasonCode;

class MqttClient {

    protected int $timeSincePing = 0;

    protected ClientConfig $clientConfig;

    protected Client $client;

    protected array $inputConfig = [
        'connect' => [
            'clean' => true,
            'will' => []
        ],
        'topics' => []
    ];

    public function __construct(protected Config $config) {
    }

    public function start() {
        $this->inputConfig['connect'] = $this -> config -> get('connect');
        $this->inputConfig['topics'] = $this -> config -> get('topics');

        $this->clientConfig = new ClientConfig($this -> config -> all());
        $this->clientConfig -> setKeepAlive($this->config -> get('keep_alive', 0));
        $this->connection();
    }

    /**
     * 链接服务端
     * @return bool
     * @throws InvokeClassException
     * @throws InvokeFunctionException
     */
    protected function connection(): bool {
        $config = $this -> callback($this->config -> get('connectBefore'));

        $this->client = new Client(
            $this -> config -> get('host'),
            $this -> config -> get('port'),
            $this -> clientConfig,
            $this -> config -> get('clientType')
        );

        $this->inputConfig = !is_array($config) || empty($config) ? $this->inputConfig : $config;
        if (!$res = $this->client->connect(...array_values($this->inputConfig['connect'] ?? []))) {
            logs('error', 'connect failed. Error', $res) -> update();
            return false;
        }
        return true;
    }


    public function wait(): void {

        if ($this -> client -> getClient() -> isConnected()) {
            $this->subscribe($this->inputConfig['topics']);
        }

        while ($this->client -> getClient() -> isConnected()) {
            $packet = $this->client -> recv();
            if ($packet && $packet !== true) {
                $this -> timeSincePing = time();
                $this -> callback($this->config -> get('event'), [ $this, $packet ]);
            }
            if (!$this -> ping()) break;
        }

        // 掉线重连
        $this -> reConnection();
    }


    public function reConnection(): void {
        $this -> callback($this->config -> get('closeConnection'));
        $this -> connection();
        $this -> wait();
    }


    /**
     * @param string $class
     * @param array $params
     * @return mixed
     * @throws InvokeClassException
     * @throws InvokeFunctionException|AttributeTypeException
     */
    protected function callback(string $class, array $params = []): mixed {
        if (!class_exists($class)) return [];
        return Container::getInstance() -> make($class, isNew: true) -> handle(...$params);
    }


    /**
     * 设置topic主题
     * @param string $topicName
     * @param array $topic
     * @return MqttClient
     */
    public function setTopic(string $topicName, array $topic): static {
        $this -> inputConfig['topics'][$topicName] = $topic;
        return $this;
    }

    /**
     * 设置连接配置信息
     * @param array $connect
     * @return $this
     */
    public function setConnection(array $connect): static {
        $this->inputConfig['connect'] = $connect;
        return $this;
    }

    /**
     * 关闭连接
     * @param int $code
     * @param array $properties
     * @return bool
     */
    public function close(int $code = ReasonCode::NORMAL_DISCONNECTION, array $properties = []): bool {
        return $this->client -> close($code, $properties);
    }

    /**
     * MQTT 订阅
     * @param array $topics
     * @param array $properties
     * @return bool|array
     */
    public function subscribe(array $topics = [], array $properties = []): bool|array
    {
        $sub = $this->client -> subscribe($topics, $properties);
        if ($sub) return $sub;
        return false;
    }

    /**
     * 取消订阅
     * @param array $topics
     * @param array $properties
     * @return bool|array
     */
    public function unsubscribe(array $topics, array $properties = []): bool|array
    {
        $un = $this->client -> unSubscribe($topics, $properties);
        if ($un) return $un;
        return false;
    }

    /**
     * 发送 PING
     * @return bool
     */
    protected function ping(): bool {
        $pingTime = $this -> config -> get('ping_time');
        if ($pingTime !== 0 && $this -> timeSincePing <= time() - $pingTime) {
            $buffer = $this -> client -> ping();
            return !!$buffer;
        }
        return true;
    }

    /**
     * 发送信息
     * @param array $data
     * @param bool $response
     * @return array|bool
     */
    public function send(array $data = [], bool $response = true): array|bool
    {
        return $this->client -> send($data, $response);
    }

    /**
     * 发布
     * @param string $topic
     * @param string $message
     * @param int $qos
     * @param int $dup
     * @param int $retain
     * @param array $properties
     * @return array|bool
     */
    public function publish(
        string $topic,
        string $message,
        int $qos = 0,
        int $dup = 0,
        int $retain = 0,
        array $properties = []
    ): bool|array {
        return $this->client -> publish(
            $topic, $message, $qos, $dup, $retain, $properties
        );
    }

    /**
     * @return Client
     */
    public function getClient(): Client {
        return $this->client;
    }

    /**
     * @return Config
     */
    public function getConfig(): Config {
        return $this->config;
    }

}