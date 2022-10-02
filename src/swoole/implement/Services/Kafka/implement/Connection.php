<?php


namespace iflow\swoole\implement\Services\Kafka\implement;

use iflow\swoole\implement\Services\Kafka\Services;
use RdKafka\Conf;
use RdKafka\Message;

abstract class Connection
{

    protected array $config = [
        'broker' => [
            '127.0.0.1:9092'
        ],
        'security_protocol' => 'ssl',
        'client_pem' => '',
        'client_key' => '',
        'ca_pem' => '',
        'group_id' => 'consumer',
        'topic' => '',
        'acks' => [
            '127.0.0.1:9092'
        ],
        'timeout' => 1000
    ];
    protected string $type  = 'consumer';
    public Conf $conf;
    public Message $message;
    protected int $error;


    public function __construct(
        protected Services $service
    ) {
        $this->config = config('swoole.kafka@'. $this->type);
        $this->connectionKafka();
    }

    protected function connectionKafka(): static {
        return $this->configKafka() -> KafkaInit();
    }

    abstract protected function KafkaInit();

    protected function configKafka(): static {
        $this->conf = new Conf();
        return $this
            -> metadataBrokerList($this->config['broker'])
            -> securityProtocol($this->config['security_protocol'])
            -> sslCertificateLocation($this->config['client_pem'])
            -> sslKeyLocation($this->config['client_key'])
            -> sslCaLocation($this->config['ca_pem']);
    }


    public function metadataBrokerList($broker = []): static
    {
        $this->conf->set('metadata.broker.list', $broker);
        return $this;
    }

    public function setGroupId(string $groupId = ''): static
    {
        $this->conf->set('group.id', $groupId);
        return $this;
    }

    public function securityProtocol($security_protocol): static
    {
        $this->conf->set('security.protocol', $security_protocol);
        return $this;
    }

    public function sslCertificateLocation(string $client_pem): static
    {
        $this->conf->set('ssl.certificate.location', $client_pem);
        return $this;
    }

    public function sslKeyLocation(string $client_key): static
    {
        $this->conf->set('ssl.key.location', $client_key);
        return $this;
    }

    public function sslCaLocation(string $ca_pem): static
    {
        $this->conf->set('ssl.ca.location', $ca_pem);
        return $this;
    }

    abstract public function setTopic($topic): static;
    abstract public function setAcks(int $acks = 0): static;
}