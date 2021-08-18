<?php


namespace iflow;

use iflow\fileSystem\lib\upLoadFile;
use iflow\request\RequestTrait;
use Psr\Http\Message\RequestInterface;

class Request
{

    use RequestTrait;

    /**
     * 初始化Request类
     * @param $request
     * @return $this
     */
    public function initializer($request): static
    {
        $this->request = $request;
        $this->server = $request -> server;
        $this->request_uri =
            str_replace("//", "/", explode('?', $request -> server['path_info'] ?? $request -> server['request_uri'])[0]);

        // 设置HTTP VERSION
        $this->version = explode('/', $this->server['server_protocol'])[1] ?? '1.1';
        $this->query_string = $request -> server['query_string'] ?? '';
        $this->request_method = $request -> server['request_method'];

        return $this->initFile();
    }

    /**
     * 初始化上传文件
     * @return $this
     */
    protected function initFile(): static
    {
        $files = $this->request -> files ?? [];
        $upLoadFile = app() -> make(upLoadFile::class);
        foreach ($files as $key => $value) {
            $upLoadFile -> setFile($key, $value);
        }
        return $this;
    }

    /**
     * 获取标准 PSR7 Request
     * @return RequestInterface
     */
    public function getRequestPsr7(): RequestInterface
    {
        if ($this->requestPsr7 !== null) $this->requestPsr7;
        $this->requestPsr7 = new \GuzzleHttp\Psr7\Request(
            $this->request_method,
            $this->getRequestUri(),
            $this->getHeader(),
            null,
            $this->version
        );
        return $this->requestPsr7;
    }

    /**
     * 请求原生 Request 方法
     * @param string $name
     * @param array $arguments
     * @return false|mixed
     */
    public function __call(string $name, array $arguments)
    {
        // TODO: Implement __call() method.
        return call_user_func([$this->request, $name], ...$arguments);
    }
}