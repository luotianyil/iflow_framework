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
        $this->request_uri = explode('?', $request -> server['request_uri'])[0];
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
    public function has($param, $type = 'get'): bool
    {
        if (!in_array($type, ['post', 'get', 'header'])) {
            return false;
        }
        return !empty($this->request -> {$type}[$param]);
    }

    // get param
    public function getParams(string $name = '')
    {
        if ($name === '') return $this->request -> get;
        return $this->get($name, 'get');
    }

    public function getHeader(string $name = '')
    {
        if ($name === '') return $this->request -> header;
        return $this->get(strtolower(str_replace('_', '-', $name)), 'header');
    }

    protected function get(string $name, string $type) {
        if ($this->has($name, $type)) {
            return $this->request -> {$type}[$name];
        }
        return null;
    }

    public function file(string $name = ''): upLoadFile|array
    {
        $upLoadFile = app() -> make(upLoadFile::class);
        return $name === '' ? $upLoadFile -> getFileList() : $upLoadFile -> getFile($name);
    }

    public function postParams(string $name = '')
    {
        if (!$this->isPost()) return [];
        $row = $this->request -> getContent();
        $params = is_array($row) ? $row : (json_decode($row, true)?: $this->request -> post);
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

    public function isAjax(): bool
    {
        $value = $this->getHeader('HTTP_X_REQUESTED_WITH') ?: $this->getHeader('X-Requested-With');
        return $value && 'xmlhttprequest' == strtolower($value);
    }

    public function getLanguage(): string
    {
        return explode(',', $this->getHeader('Accept-Language'))[0];
    }

    public function __call(string $name, array $arguments)
    {
        // TODO: Implement __call() method.
        return call_user_func([$this->request, $name], ...$arguments);
    }
}