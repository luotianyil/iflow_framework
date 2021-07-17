<?php


namespace iflow\Swoole\MQTT\lib;

use Simps\MQTT\Client;
use iflow\Swoole\MQTT\Services;
use Simps\MQTT\Config\ClientConfig;
use Simps\MQTT\Hex\ReasonCode;

class mqttClient
{
    protected Client $client;
    protected array $inputConfig = [
        'connect' => [
            'clean' => true,
            'will' => []
        ],
        'topics' => []
    ];

    protected array $config = [];

    // PING 定时id
    protected int $pingTimerId;
    protected int $timeSincePing = 0;

    /**
     * @param mixed $services MQTT CONFIG
     */
    public function initializer(array|Services $services)
    {
        // 初始化连接
        $this->inputConfig['connect'] = $services['connect'] ?? [];
        $this->inputConfig['topics'] = $services['topics'] ?? [];
        $this->config = $services;


        $config = new ClientConfig($services);
        $this->client = new Client(
            $services['host'],
            $services['port'],
            $config,
            $services['sockType']
        );

        // 等待连接成功
        while (!$this->connect()) {
            \Swoole\Coroutine::sleep(1);
        }
    }

    // 发送Connection请求
    protected function connect(): bool
    {
        $inputConfig = $this -> callConfigHandle($this-> config['connectBefore'], [$this]);

        $this->inputConfig = empty($inputConfig) ? $this->inputConfig : $inputConfig;

        if (!$res = $this->client->connect(...array_values($this->inputConfig['connect'] ?? []))) {
            logs('error', 'connect failed. Error', $res);
            return false;
        }
        return true;
    }

    // 发送PING
    protected function ping()
    {
        if (isset($this -> config['keep_alive']) && $this -> timeSincePing < (time() - $this -> config['keep_alive'])) {
            $buffer = $this -> client -> ping();
            if ($buffer) $this -> timeSincePing = time();
            else $this -> close();
        }
    }

    /**
     * 获取Swoole Client
     * @return Client
     */
    public function getClient(): Client
    {
        return $this->client;
    }

    /**
     * 设置topic主题
     * @param string $topicName
     * @param array $topic
     * @return mqttClient
     */
    public function setTopic(string $topicName, array $topic): static
    {
        $this -> inputConfig['topics'][$topicName] = $topic;
        return $this;
    }

    /**
     * 设置连接配置信息
     * @param array $connect
     * @return $this
     */
    public function setConnection(array $connect): static
    {
        $this->inputConfig['connect'] = $connect;
        return $this;
    }

    /**
     * 关闭连接
     * @param int $code
     * @param array $properties
     * @return bool
     */
    public function close(int $code = ReasonCode::NORMAL_DISCONNECTION, array $properties = []): bool
    {
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
        $sub =  $this->client -> subscribe($topics, $properties);
        if ($sub) return $sub;
        return false;
    }

    /**
     * 取消订阅
     * @param array $topics
     * @param array $properties
     * @return bool
     */
    public function unsubscribe(array $topics, array $properties = []): bool|array
    {
        $un = $this->client -> unSubscribe($topics, $properties);
        if ($un) return $un;
        return false;
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
    ): bool|array
    {
        return $this->client -> publish(
            $topic, $message, $qos, $dup, $retain, $properties
        );
    }

    protected function callConfigHandle($object = '', $param = [])
    {
        if (class_exists($object)) {
            return call_user_func([new $object, 'Handle'], ...$param);
        }
        return [];
    }
}