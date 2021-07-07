<?php

namespace iflow;

use iflow\exception\lib\HttpResponseException;
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

    public function contentType($contentType = 'application/json') : static {
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
                if (!is_string($response -> {$key})) $response -> {$key}($value);
            }
        }
        return $response;
    }

    public function notFount(string $msg = '404 Not-Found'): bool
    {
        $this -> code = 404;
        if (request() -> isAjax() === false) {
            $path = config('app@404_error_page');
            if (file_exists($path)) {
                throw new HttpResponseException($this->sendFile($path, false));
            }
        }
        throw new HttpResponseException(message() -> nodata($msg));
    }

    public function send(): bool
    {
        if ($this->response === null) return false;

        // Swoole 验证是否已经结束请求
        if ($this->response -> isWritable() === false) return false;
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
        return sendFile($path, isConfigRootPath: $isConfigRootPath);
    }

    public function setLastModified(string $value = ""): static
    {
        $this->headers([
            'Last-Modified' => $value ?: gmdate('D,d M Y H:i:s')."GMT"
        ]);
        return $this;
    }

    public function setCacheControl(string $value = ""): static
    {
        $this->headers([
            'Cache-Control' => $value ?: "max-age=36000,must-revalidata"
        ]);
        return $this;
    }

    public function steExpiresTimes(string $value = ""): static
    {
        $this->headers([
            'Expires' => $value ?: gmdate('D,d M Y H:i:s',time() + 36000)."GMT"
        ]);
        return $this;
    }

    protected function setResponseHeader()
    {
        $this->response -> status($this->code);
        $this->response -> header('Content-Type', $this->contentType . ';' . $this->charSet);
        foreach ($this->headers as $key => $value) {
            $this->response -> header($key, $value);
        }
    }

    public function initializer($response): static
    {
        $this->response = $response;
        return $this;
    }

    public function trailer(string $key, string $value, bool $ucwords = true): static
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
