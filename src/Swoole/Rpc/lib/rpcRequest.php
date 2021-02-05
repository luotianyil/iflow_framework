<?php


namespace iflow\Swoole\Rpc\lib;


use Swoole\Coroutine\Client as SwooleClient;

class rpcRequest
{
    protected ?object $client = null;
    protected string $error = "";
    protected mixed $data = "";

    public function __construct(
        protected string $host = '',
        protected int $port = 0,
        protected string $url = '',
        protected array $param = [],
        protected array $options = []
    ) {
    }

    public function request()
    {
        $this->param['request_uri'] = $this->url;
        $this->client = $this->client ?: new SwooleClient(SWOOLE_SOCK_TCP);
        $this->client -> set($this->options);
        if (!$this->client -> connect($this->host, $this->port)) {
            $this->error = $this->client -> errMsg;
        } else {
            $this->client -> send(json_encode($this->param, JSON_UNESCAPED_UNICODE));
            $this->data = $this->client -> recv();
            $this->client -> close();
        }
    }

    public function getData()
    {
        return json_decode($this->data, true) ?? $this->data;
    }

    public function getError()
    {
        return $this->error;
    }
}