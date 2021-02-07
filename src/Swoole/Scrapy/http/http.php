<?php


namespace iflow\Swoole\Scrapy\http;


use Co\Http\Client;


/**
 * Class http
 * @package iflow\Swoole\Scrapy\http
 */
class http
{

    protected Client|\Co\Http2\Client $client;

    protected mixed $data = null;

    public function __construct(
        protected string $host,
        protected int $port = 0,
        protected string $method = 'GET',
        protected array $header = [],
        protected bool $isSsl = false,
        protected array $options = []
    ){}

    public function connection(): static
    {
        $param = [];
        $param[] = $this->host;

        if ($this->port !== 0) {
            $param[] = $this->port;
        }
        $param[] = $this->isSsl;
        return $this->initClient($param);
    }

    protected function initClient($param): static
    {
        $this->client = new Client(...$param);
        $this->client -> set($this->options);
        return $this;
    }

    public function request(string $path, mixed $data): static
    {
        $this->setHeader($this->header);
        $this->client -> setHeaders($this->header);
        $this->client -> setMethod($this->method);
        $this->client -> setData($data);
        $this->client -> execute($path);
        $this->data = $this->client -> body;
        $this->client -> close();
        return $this;
    }

    public function getData()
    {
        return match (gettype($this->data)) {
            'string' => json_decode($this->data, true) ?? $this->data,
            default => $this->data
        };
    }

    public function setHeader(array $header = []): static
    {
        $this->header = array_replace_recursive($this->header, $header) ?? [];
        return $this;
    }

    public function __call(string $name, array $arguments)
    {
        // TODO: Implement __call() method.
        if (method_exists($this->client, $name)) return call_user_func([$this->client, $name], $arguments);
        return null;
    }

    public function __get(string $name)
    {
        // TODO: Implement __get() method.
        if (property_exists($this->client, $name)) return $this->client -> $name;
        return null;
    }
}
