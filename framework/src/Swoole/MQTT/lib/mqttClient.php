<?php


namespace iflow\Swoole\MQTT\lib;

use Simps\MQTT\Client;
use iflow\Swoole\MQTT\Services;

class mqttClient
{

    private Client $client;
    public Services $services;
    protected array $inputConfig = [
        'connect' => [
            'clean' => true,
            'will' => []
        ],
        'topics' => [
            'test' => [
                'qos' => 1,
                'no_local' => true,
                'retain_as_published' => true,
                'retain_handling' => 2,
            ]
        ]
    ];

    protected $pingTimerId;
    private int $timeSincePing = 0;

    public function initializer(Services $services)
    {
        $this->services = $services;
        \Co\run(function () {
            $this->client = new Client($this->services -> configs, $this->services -> configs['swConfig'], $this->services -> configs['sockType'], 2);
            if ($this->connect()) {
                if ($this->client -> subscribe($this->inputConfig['topics'])) {
                    $this->wait();
                } else {
                    throw new \Exception('MQTT Subscribe Error');
                }
            }
        });
    }

    private function wait()
    {
        while(true)
        {
            $packet = $this->client->recv();
            if ($packet && $packet !== true) {
                $this -> timeSincePing = time();
                $this -> services->callConfigHandle($packet, [$this, $packet]);
            }
            $this -> ping();
        }
    }

    private function connect(): bool
    {
        $inputConfig = $this -> services ->callConfigHandle($this->services -> config['connectBefore'], [$this]);

        $this->inputConfig = empty($inputConfig) ? $this->inputConfig : $inputConfig;

        if (!$this->client->connect(...array_values($this->inputConfig['connect'] ?? []))) {
            $this->services -> Console -> outPut -> writeLine("connect failed. Error");
            return false;
        }
        return true;
    }

    private function ping()
    {
        if (isset($this->services -> config['keep_alive']) && $this -> timeSincePing < (time() - $this->services -> config['keep_alive'])) {
            $buffer = $this -> client ->ping();
            if ($buffer) $this -> timeSincePing = time();
            else $this->client -> close();
        }
    }

    public function getClient(): Client
    {
        return $this->client;
    }
}