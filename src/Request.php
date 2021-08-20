<?php


namespace iflow;

use iflow\fileSystem\lib\upLoadFile;
use iflow\request\RequestTrait;

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
        $url = $request -> server['path_info'] ?? $request -> server['request_uri'];

        // 初始化 原生Request
        $this->request = $request;
        $this->server = $request -> server;
        $this->request_uri = str_replace("//", "/", explode('?', $url)[0]);

        // 设置HTTP VERSION
        $this->version = str_replace('HTTP/', '', $this->server['server_protocol']);
        $this->query_string = explode('?', $url)[1] ?? '';

        // 获取请求方法
        $this->request_method = $request -> server['request_method'];

        return $this
            -> initRequestParams()
            -> initFile();
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