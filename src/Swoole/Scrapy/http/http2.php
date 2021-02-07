<?php


namespace iflow\Swoole\Scrapy\http;


use Co\Http2\Client;
use Swoole\Http2\Request;

class http2 extends http
{

    protected Request $request;

    protected function initClient($param): static
    {
        $this->client = new Client(...$param);
        $this->client -> set($this->options);
        $this->request = new Request();
        $this->request -> method = $this->method;
        return $this;
    }

    public function request(string $path = "", mixed $data = ""): static
    {
        $this->client->connect();
        $this->request -> path = $path;
        $this->request -> headers = $this->header;
        $this->request -> data = !is_string($data) ? json_encode($data, JSON_UNESCAPED_UNICODE) : $data;

        $this->client->send($this->request);
        $this->data = $this->client->recv();
        $this->client -> close();
        return $this;
    }
}
