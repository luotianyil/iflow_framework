<?php


namespace iflow;

class Request
{

    public \Swoole\Http\Request $request;
    public array $server;
    public string $request_uri = '';
    public string $query_string = '';
    public string $request_method = '';

    public function initializer($request): static
    {
        $this->request = $request;
        $this->server = $request -> server;
        $this->request_uri = $request -> server['request_uri'];
        $this->query_string = $request -> server['query_string'] ?? '';
        $this->request_method = $request -> server['request_method'];
        return $this;
    }

    // validate param
    public function has($param, $type = 'get')
    {
        if (!in_array($type, ['post', 'get'])) {
            return false;
        }
        return !empty($request -> $type[$param]);
    }

    // get param
    public function getParams(string $param = '', $type = 'get')
    {
        if ($param === '') return $this->request -> $type;
        return $this->request -> $type[$param];
    }

    public function isPost(): bool
    {
        return strtoupper($this->request_method) === 'POST';
    }

    public function isGet(): bool
    {
        return strtoupper($this->request_method) === 'GET';
    }

    public function isPut(): bool
    {
        return strtoupper($this->request_method) === 'PUT';
    }

    public function isDelete(): bool
    {
        return strtoupper($this->request_method) === 'DELETE';
    }

    public function isOptions(): bool
    {
        return strtoupper($this->request_method) === 'OPTIONS';
    }
}