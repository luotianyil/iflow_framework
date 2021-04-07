<?php


namespace iflow;


use iflow\response\lib\File;

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
        if (request() -> isAjax() === false) {
            $path = config('app@404_error_page');
            if (file_exists($path)) {
                return $this->sendFile($path, false) -> send();
            }
        }
        return message() -> nodata('404 Not-Found') -> send();
    }

    public function send()
    {
        $this->setResponseHeader();
        return $this->response -> end($this->output($this->data));
    }

    /**
     * 设置重定向地址
     * @param string $url
     * @return $this
     */
    public function setRedirect(string $url = ""): static
    {
        $this->code = 302;
        $this->headers["Location"] = $url;
        return $this;
    }

    /**
     * 发送文件
     * @param string $path
     * @param bool $isConfigRootPath
     * @return File
     */
    private function sendFile(string $path = '', bool $isConfigRootPath = true): File
    {
        $this->setResponseHeader();
        return sendFile($path, isConfigRootPath: $isConfigRootPath);
    }

    protected function setResponseHeader()
    {
        foreach ($this->headers as $key => $value) {
            $this->response -> header($key, $value);
        }
        $this->response -> status($this->code);
        $this->response -> header('Content-Type', $this->contentType . ';' . $this->charSet);
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

    public function __call(string $name, array $arguments)
    {
        // TODO: Implement __call() method.
        if (method_exists($this->response, $name)) return call_user_func($name, ...$arguments);
        return null;
    }

}