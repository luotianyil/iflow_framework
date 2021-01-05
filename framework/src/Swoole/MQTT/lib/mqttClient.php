<?php


namespace iflow\Swoole\MQTT\lib;


use BinSoul\Net\Mqtt\Packet\PublishRequestPacket;
use iflow\Swoole\MQTT\Services;
use Swoole\Coroutine\Client;

class mqttClient
{

    protected Client $client;
    protected Services $services;

    public function initializer(Services $services)
    {
        $this->services = $services;

        $this->client = new Client(SWOOLE_SOCK_TCP);
        $this->client -> set($this->services -> options);

        \Co\run(function () {
            if ($this->connect()) {
                $this->wait();
            }
            $this->close();
        });
    }

    public function send()
    {
        $request = new PublishRequestPacket;
        $request->setTopic(1);
        $request->setPayload(json_encode(['code' => 255], true));
        $request->setQosLevel(1);
        $request->setDuplicate(1);
        $request->setRetained(1);
        $request->setIdentifier(1);
        $this->client -> send($request);
    }

    public function wait()
    {}

    public function connect(): bool
    {
        if (!$this->client->connect(...$this->services -> param)) {
            $this->services -> Console -> outPut -> writeLine("connect failed. Error: {$this->client->errCode}");
            return false;
        }
        return true;
    }

    public function isConnect(): bool
    {
        return $this->client -> isConnected();
    }

    public function close(): bool
    {
        if ($this->isConnect()) {
            return $this->client -> close();
        }
        return false;
    }

}