<?php


namespace iflow\Swoole\dht\lib;


class node
{

    public function __construct(
        protected string $NodeId = '',
        protected string $ip = '',
        protected int $port = 0
    ){}


    /**
     * @return string
     */
    public function getIp(): string
    {
        return gethostbyname($this->ip);
    }

    /**
     * @return string
     */
    public function getNodeId(): string
    {
        return $this->NodeId;
    }

    /**
     * @return int
     */
    public function getPort(): int
    {
        return $this->port;
    }

}