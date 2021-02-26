<?php


namespace iflow\Swoole\Scrapy\http;


use Co\Http2\Client;
use Swoole\Http2\Request;

class http2 extends http
{

    protected Request $request;

    protected function initClient($param, $options = []): static
    {
        $this->client = new Client(...$param);
        $options['options'] = array_replace_recursive($this->options, $options['options']);
        $this->client -> set($options['options']);
        $this->request = new Request();
        return $this;
    }

    public function request($path = ""): static
    {
        $this->client->connect();
        $this->request -> path = $path;
        $this->client->send($this->request);
        $this->data = $this->client->recv();
        $this->client -> close();
        return $this;
    }

    public function setHeader(array $header = []): static
    {
        $this->header = array_replace_recursive($this->header, $header) ?? [];
        $this->request -> headers = $this->header;
        return $this;
    }

    protected function setData($query, $data): static
    {
        if ($query) {
            parse_str($query, $queryArray);
            $data = array_replace_recursive($queryArray, $data);
        }
        $this->request -> data = is_array($data) ? http_build_query($data): $data;
        return $this;
    }

    public function setMethod(string $method = ''): static
    {
        $this->request -> method = $method;
        return $this;
    }
}
