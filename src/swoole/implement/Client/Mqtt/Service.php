<?php

namespace iflow\swoole\implement\Client\Mqtt;

use iflow\Container\implement\annotation\tools\data\Inject;
use iflow\swoole\abstracts\ServicesAbstract;
use iflow\swoole\implement\Client\implement\Events\Loop;
use iflow\swoole\implement\Tools\Pool\Process;
use Simps\MQTT\Client;
use Simps\MQTT\Config\ClientConfig;
use Simps\MQTT\Hex\ReasonCode;
use function Co\run;

class Service extends ServicesAbstract {

    protected int $timeSincePing = 0;

    protected ClientConfig $clientConfig;

    #[Inject]
    public Loop $loop;

    #[Inject]
    public Process $process;

    protected array $inputConfig = [
        'connect' => [
            'clean' => true,
            'will' => []
        ],
        'topics' => []
    ];

    public function start() {

        $this -> setPid($this->config -> get('swConfig@pid_file'))
            -> setServerParams();

        $this->inputConfig['connect'] = $this->servicesCommand -> config -> get('connect');
        $this->inputConfig['topics'] = $this->servicesCommand -> config -> get('topics');
        $clientType = $this->servicesCommand -> config -> get('clientType');

        $this->clientConfig = new ClientConfig($this->servicesCommand -> config -> all());
        $this->clientConfig -> setKeepAlive($this->config['keep_alive'] ?? 0);

        $this->createClient($clientType) -> servicesCommand -> setServices();
        $this->printStartContextToConsole('mqtt');

        $this->wait();
        $this->loop -> wait();
    }


    protected function createClient(int $clientType): static {
        try {
            $this->SwService = new Client(
                $this->servicesCommand -> config -> get('host'),
                $this->servicesCommand -> config -> get('port'),
                $this->clientConfig,
                $clientType
            );
        } catch (\Exception $exception) {
            run(function () use ($clientType) {
                $this->SwService = new Client(
                    $this->servicesCommand -> config -> get('host'),
                    $this->servicesCommand -> config -> get('port'),
                    $this->clientConfig,
                    $clientType
                );
            });
        }

        return $this;
    }

    protected function connection(): bool {
        $config = $this->servicesCommand -> callConfHandle(
            $this->servicesCommand -> config -> get('connectBefore', ''),
            [ $this ]
        );

        $this->inputConfig = !is_array($config) || empty($config) ? $this->inputConfig : $config;

        if (!$res = $this->SwService->connect(...array_values($this->inputConfig['connect'] ?? []))) {
            logs('error', 'connect failed. Error', $res) -> update();
            return false;
        }

        return true;
    }

    protected function wait(): void {
        if ($this->connection()) {
            if ($this->subscribe($this->inputConfig['topics'])) {
                $this->createELoop();
            } else {
                $this->close();
                $this->servicesCommand -> Console -> writeConsole -> writeLine('Subscribe Topics Failed');
            }
        }
    }

    protected function createELoop() {
        while ($this->SwService -> getClient() -> isConnected()) {
            $packet = $this->SwService -> recv();
            if ($packet && $packet !== true) {
                $this -> timeSincePing = time();
                $this -> servicesCommand->callConfHandle($this->getEventClass(), [ $this, $packet ]);
            }
            $this -> ping();
        }
    }

    /**
     * 发送 PING
     * @return void
     */
    protected function ping(): void {
        if (isset($this -> config['keep_alive']) && $this -> timeSincePing < (time() - $this -> config['keep_alive'])) {
            $buffer = $this -> SwService -> ping();
            if ($buffer) $this -> timeSincePing = time();
            else $this -> close();
        }
    }

    /**
     * 设置topic主题
     * @param string $topicName
     * @param array $topic
     * @return Service
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
    public function close(int $code = ReasonCode::NORMAL_DISCONNECTION, array $properties = []): bool {
        return $this->SwService -> close($code, $properties);
    }

    /**
     * MQTT 订阅
     * @param array $topics
     * @param array $properties
     * @return bool|array
     */
    public function subscribe(array $topics = [], array $properties = []): bool|array
    {
        $sub = $this->SwService -> subscribe($topics, $properties);
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
        $un = $this->SwService -> unSubscribe($topics, $properties);
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
        return $this->SwService -> send($data, $response);
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
        return $this->SwService -> publish(
            $topic, $message, $qos, $dup, $retain, $properties
        );
    }

    protected function getSwooleServiceClass(): string {
        return Client::class;
    }

}