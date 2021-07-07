<?php


namespace iflow\Swoole\Rpc\lib;

use iflow\socket\lib\client\Client;

class rpcRequest
{
    protected ?object $client = null;
    protected string $error = "";
    protected mixed $data = "";

    public function __construct(
        protected string $host = '',
        protected int $port = 0,
        protected string $url = '',
        protected bool $isSsl = false,
        protected array $param = [],
        protected array $options = []
    ) {}

    public function request()
    {
        $this->param['request_uri'] = $this->url;

        if ($this->client === null) {
            if (class_exists(\Swoole\Coroutine\Client::class)) {
                $this->client = new \Swoole\Coroutine\Client(
                    $this->isSsl ? SWOOLE_TCP | SWOOLE_SSL : SWOOLE_TCP
                );
            } else {
                $this->client = new Client($this->isSsl);
            }
        }
        $this->client -> set($this->options);
        if (!$this->client -> connect($this->host, $this->port, 0.5)) {
            $this->error = $this->client -> errMsg;
        } else {
            $this->client -> send(json_encode($this->param, JSON_UNESCAPED_UNICODE));
            $this->data = $this->client -> recv(30);
            $this->error = $this->client -> close() ? '' : 'Close Connection Fail';
        }
    }

    public function getData()
    {
        return json_decode($this->data, true) ?? $this->data;
    }

    public function getError(): string
    {
        return $this->error;
    }
}