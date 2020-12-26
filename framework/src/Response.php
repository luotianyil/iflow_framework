<?php


namespace iflow;


class Response
{

    protected string $contentType = 'text/html';
    protected string $charSet = 'utf-8';
    protected int $code = 200;
    protected mixed $data;
    protected array $options = [];
    protected array $headers = [];

    protected function init($data, $code) {
        $this->code = $code;
        $this->data($data);
    }

    protected function contentType($contentType = 'application/json') : static {
        $this -> contentType = $contentType;
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
        return app() -> invokeClass($class, [$data, $code]);
    }

}