<?php


namespace iflow\swoole\implement\Services\Kafka\implement;


use iflow\Utils\Tools\Timer;
use RdKafka\Exception;
use RdKafka\KafkaConsumer;
use function Co\run;

class Consumer extends Connection {

    protected string $type  = 'consumer';

    public KafkaConsumer $consumer;

    public function handle() {
        run(function () {
            Timer::tick($this->config['sleepTime'], function () {
                $this->message = $this->consumer -> consume($this->config['timeout']);
                if (class_exists($this->config['Handle'])) {
                    app($this->config['Handle']) -> handle($this->message);
                }
            });
        });
    }

    protected function KafkaInit(): static {
        // TODO: Implement KafkaInit() method.
        $this -> setGroupId($this->config['group_id']);
        $this -> consumer = new KafkaConsumer($this->conf);
        return $this->setTopic($this->config['topic'])
                -> setAcks($this->config['acks'])
                -> offsetStoreMethod($this->config['offsetStoreMethod'] ?? 'file');
    }

    /**
     * @param $topic
     * @return static
     * @throws Exception
     */
    public function setTopic($topic): static {
        $this -> consumer -> subscribe($topic);
        return $this;
    }

    public function setAcks(int $acks = 0): static {
        // TODO: Implement setAcks() method.
        $this->conf -> set('request.required.acks', $acks);
        return $this;
    }

    public function offsetStoreMethod(string $method = 'file'): static {
        $this->conf -> set('offset.store.method', $method);
        return $this;
    }
}