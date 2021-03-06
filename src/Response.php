<?php


namespace iflow;


class Response
{
    public string $contentType = 'text/html';
    public string $charSet = 'utf-8';
    public int $code = 200;
    public mixed $data;
    public array $options = [];
    public array $headers = [];
    public mixed $response = null;

    protected function init($data, $code) {
        $this->code = $code;
        $this->data($data);
    }

    protected function contentType($contentType = 'application/json') : static {
        $this -> contentType = $contentType;
        return $this;
    }

    protected function response($response): static {
        $this->response = $response;
        return $this;
    }

    public function data($data): static
    {
        $this->data = $data;
        return $this;
    }

    public function charSet(string $charSet = 'utf-8')
    {
        $this->charSet = $charSet;
    }

    public function options($options = []): static
    {
        $this->options = array_merge($this->options, $options);
        return $this;
    }

    public function headers(array $headers = []): static
    {
        $this->headers = array_merge($this->headers, $headers);
        return $this;
    }

    public function output($data)
    {
        return $data;
    }

    public static function create($data = [], int $code = 200, string $type = 'json')
    {
        $class = str_contains($type, '//') ? $type : '\\iflow\\response\\lib\\'.ucfirst($type);
        $response = Container::getInstance()->invokeClass($class, [$data, $code]);
        $response -> response = response() -> response;
        foreach (array_merge((array) $response, (array) response()) as $key => $value) {
            if (method_exists($response, $key)) {
                if (!is_string($response -> $key)) $response -> $key($value);
            }
        }
        return $response;
    }

    public function notFount(): response\lib\File | bool
    {
        $this -> code = 404;
        $path = config('app@404_error_page');
        if (file_exists($path)) {
            return $this -> response -> sendFile($path);
        } else $this -> data('404 Not-Found') -> send();
        return false;
    }

    public function send()
    {
        $this->setResponseHeader();
        return $this->response -> end($this->output($this->data));
    }

    public function sendFile(string $path = '')
    {
        return sendFile($path);
    }

    protected function setResponseHeader()
    {
        foreach ($this->headers as $key => $value) {
            $this->response -> header($key, $value);
        }
        $this->response -> status($this->code);
        $this->response -> header('content-type', $this->contentType);
    }

    public function initializer($response): static
    {
        $this->response = $response;
        return $this;
    }

    public function trailer(string $key, string $value, bool $ucwords = true)
    {
        $this->response -> trailer(...func_get_args());
        return $this;
    }

}