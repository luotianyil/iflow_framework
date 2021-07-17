<?php


namespace iflow\Swoole\MQTT\lib;


use iflow\Swoole\MQTT\Services;

class mqttClientCommand extends mqttClient
{
    public Services $services;

    /**
     * 初始化连接
     * @param $services
     */
    public function initializer(array|Services $services)
    {
        $this->services = $services;
        \Co\run(function () {
            parent::initializer($this->services -> config);
            // 连接成功 并 订阅主题 是否成功
            if ($this->subscribe($this->inputConfig['topics'])) {
                $this->wait();
            } else {
                $this->close();
                $this->services -> Console -> outPut -> writeLine('Subscribe Topics Failed');
            }
        });
    }

    // 监听主题信息
    protected function wait()
    {
        while(true)
        {
            $packet = $this->client->recv();
            if ($packet && $packet !== true) {
                $this -> timeSincePing = time();
                $this -> services->callConfigHandle($this->config['Handle'], [$this, $packet]);
            }
            $this -> ping();
        }
    }

}