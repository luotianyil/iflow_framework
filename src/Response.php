<?php

namespace iflow;

use iflow\response\lib\File;
use iflow\response\ResponseTrait;
use Psr\Http\Message\ResponseInterface;

class Response
{
    use ResponseTrait;

    protected function init($data, $code) {
        $this->code = $code;
        $this->data($data);
    }

    /**
     * 静态创建新的 Response 对象
     * @param mixed $data
     * @param int $code
     * @param string $type
     * @return object
     */
    public static function create(mixed $data = [], int $code = 200, string $type = 'json'): object {
        $class = str_contains($type, '//') ? $type : '\\iflow\\response\\lib\\'.ucfirst($type);
        return app() -> invokeClass($class, [ $data, $code ]);
    }

    /**
     * 普通输出
     * @param mixed $data
     * @return mixed
     */
    public function output(mixed $data): mixed {
        return $data;
    }

    /**
     * 结束请求发送数据
     * @return bool
     */
    public function send(): bool {
        // 获取Response 原始响应体
        $this->response = response() -> response;
        // 结束请求
        return $this->setResponseHeader() -> response -> end($this->output($this->data));
    }

    /**
     * 发送文件
     * @param string $path
     * @param bool $isConfigRootPath
     * @return File
     */
    protected function sendFile(string $path = '', bool $isConfigRootPath = true): File
    {
        return sendFile($path, isConfigRootPath: $isConfigRootPath);
    }

    /**
     * 结束响应时 设置请求头
     * @return $this
     */
    protected function setResponseHeader(): static {
        $this->response -> status($this->code);
        $this->response -> header('Content-Type', $this->contentType . ';' . $this->charSet);

        // 处理 Headers
        $this->headers(response() -> headers);
        foreach ($this->headers as $key => $value) {
            $this->response -> header($key, $value);
        }
        return $this;
    }

    /**
     * 初始化响应类
     * @param $response
     * @return $this
     */
    public function initializer($response): static {
        $this->response = $response;
        return $this;
    }

    public function trailer(string $key, string $value, bool $ucwords = true): static {
        $this->response -> trailer(...func_get_args());
        return $this;
    }

    /**
     * 获取PSR7 标准响应体
     * @param string|null $reason
     * @return ResponseInterface
     */
    public function getResponsePsr7(?string $reason = null): ResponseInterface
    {
        if ($this->responsePsr7 !== null) return $this->responsePsr7;
        $this->responsePsr7 = new \GuzzleHttp\Psr7\Response(
            $this->code,
            $this->headers,
            $this->data ?? '',
            $this->version,
            $reason
        );
        return $this->responsePsr7;
    }

    /**
     * 请求 Response 原始方法
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function __call(string $name, array $arguments): mixed
    {
        // TODO: Implement __call() method.
        if (method_exists($this->response, $name)) return call_user_func($name, ...$arguments);
        return null;
    }
}
