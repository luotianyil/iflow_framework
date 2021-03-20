<?php


namespace iflow\Swoole\Kafka\lib;


use iflow\Utils\Tools\Timer;
use RdKafka\KafkaConsumer;

class consumer extends connection
{

    protected string $type  = 'consumer';
    public KafkaConsumer $consumer;

    public function handle()
    {
        Timer::tick($this->config['sleepTime'], function () {
            $this->message = $this->consumer -> consume($this->config['timeout']);
            if (class_exists($this->config['Handle'])) {
                app($this->config['Handle']) -> handle($this->message);
            }
        });
    }

    protected function KafkaInit(): static
    {
        // TODO: Implement KafkaInit() method.
        $this -> setGroupId($this->config['group_id']);
        $this -> consumer = new \RdKafka\KafkaConsumer($this->conf);
        return $this->setTopic($this->config['topic'])
                -> setAcks($this->config['acks'])
                -> setAutoCommit($this->config['auto_commit']['enable'], $this->config['auto_commit']['ms'])
                -> offsetStoreMethod($this->config['offsetStoreMethod']);
    }

    /**
     * @param $topic
     * @return static
     * @throws \RdKafka\Exception
     */
    public function setTopic($topic): static
    {
        $this -> consumer->subscribe($topic);
        return $this;
    }

    public function setAcks(int $acks = 0): static
    {
        // TODO: Implement setAcks() method.
        $this->conf -> set('request.required.acks', $acks);
        return $this;
    }

    public function setAutoCommit(int $enable = 0, int $ms = 1000): static
    {
        $this->conf -> set('auto.commit.enable', $enable);
        $this->conf -> set('auto.commit.ms', $ms);
        return $this;
    }

    public function offsetStoreMethod(string $method = 'file'): static
    {
        $this->conf -> set('offset.store.method', $method);
        return $this;
    }
}