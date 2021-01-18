<?php


namespace iflow;

use iflow\fileSystem\lib\upLoadFile;

class Request
{

    public mixed $request;
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

        $this->initFile();
        return $this;
    }

    protected function initFile()
    {

        $files = $this->request -> files ?? [];
        $upLoadFile = app() -> make(upLoadFile::class);
        foreach ($files as $key => $value) {
            $upLoadFile -> setFile($key, $value);
        }
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
    public function getParams(string $name = '')
    {
        if ($name === '') return $this->request -> get;
        return $this->request -> get[$name] ?? null;
    }


    public function file(string $name): upLoadFile|array
    {
        $upLoadFile = app() -> make(upLoadFile::class);
        return $upLoadFile -> getFile($name);
    }

    public function postParams(string $name = '')
    {
        if (!$this->isPost()) return [];
        $params = json_decode($this->request -> getContent(), true);
        if ($name === '') return $params;
        return $params[$name] ?? null;
    }

    public function params(string $name = '')
    {
        return match ($this->isPost()) {
            true => $this->postParams($name),
            false => $this->getParams($name)
        };
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