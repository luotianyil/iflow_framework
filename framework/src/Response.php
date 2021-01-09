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

    protected function output($data): string
    {
        return $data;
    }

    public static function create($data = [], string $type = 'json', int $code = 200)
    {
        $class = str_contains($type, '//') ? $type : '\\iflow\\response\\lib\\'.ucfirst($type);
        $response = Container::getInstance()->invokeClass($class, [$data, $code]);
        foreach (array_merge((array) $response, (array) response()) as $key => $value) {
            if (method_exists($response, $key)) {
                if (!is_string($response -> $key)) $response -> $key($value);
            }
        }
        return $response;
    }

    public function notFount()
    {
        $this -> code = 404;
        $this -> data('404 Not-Found') -> send();
    }

    public function send()
    {
        foreach ($this->headers as $key => $value) {
            $this->response -> header($key, $value);
        }
        $this->response -> status($this->code);
        $this->response -> header('content-type', $this->contentType);
        $this->response -> end($this->output($this->data));
    }

    public function initializer($response): static
    {
        $this->response = $response;
        return $this;
    }

}