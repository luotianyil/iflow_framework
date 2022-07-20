<?php


namespace iflow\swoole\implement\Services\Kafka\implement;

use RdKafka\ProducerTopic;
use RdKafka\TopicConf;

class Producer extends Connection
{
    protected string $type  = 'producer';

    public \RdKafka\Producer $producer;
    public TopicConf $topicConf;
    protected string $topics = '';
    public ProducerTopic $producerTopic;


    protected function KafkaInit(): static
    {
        // TODO: Implement KafkaInit() method.
        $this->producer = new \RdKafka\Producer($this->conf);
        $this->topicConf = new TopicConf();
        $this->setAcks($this->config['acks']);
        return $this;
    }

    public function setAcks(int $acks = 0): static
    {
        $this->topicConf -> set('request.required.acks', $acks);
        return $this;
    }

    public function setTopic($topic): static
    {
        // TODO: Implement setTopic() method.
        $this->topics = $topic;
        return $this;
    }

    public function emit(int $partition = RD_KAFKA_PARTITION_UA, int $msgflags = 0, $payload = '', $key = null)
    {
        $this->topics = $this->topics === '' ? $this->config['topic'] : $this->topics;
        $this->producerTopic = $this->producer -> newTopic($this->topics, $this->topicConf);
        $this->producerTopic -> produce($partition, $msgflags, $payload, $key);
        $this->producer -> poll($this->config['timeout']);
    }
}