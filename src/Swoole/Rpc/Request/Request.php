<?php

namespace iflow\Swoole\Rpc\Request;


use iflow\socket\lib\client\Client;

/**
 * @className Request
 * @desc 发送 RPC 请求
 */
class Request {

    protected ?object $client = null;

    protected string $error = '';

    protected mixed $data = '';


    public function __construct(
        protected string $host = '',
        protected int $port = 0,
        protected string $url = '',
        protected bool $isSsl = false,
        protected array $param = [],
        protected array $options = []
    ) {
    }

    /**
     * 设置 HOST
     * @param string $host
     * @return $this
     */
    public function withHost(string $host): Request {
        $this->host = $host;
        return $this;
    }

    /**
     * 设置请求端口
     * @param int $port
     * @return $this
     */
    public function withPort(int $port): Request {
        $this->port = $port;
        return $this;
    }

    /**
     * 设置 是否启用 SSL
     * @param bool $isSsl
     * @return $this
     */
    public function withIsSsl(bool $isSsl): Request {
        $this->isSsl = $isSsl;
        return $this;
    }

    /**
     * 设置请求参数
     * @param array $params
     * @return $this
     */
    public function setQueryParams(array $params): Request {
        $this->param = $params;
        return $this;
    }

    /**
     * @param array $options
     * @return Request
     */
    public function setOptions(array $options): Request {
        $this->options = $options;
        return $this;
    }

    /**
     * 发送请求
     * @return $this
     */
    public function request(): Request {
        $this->param['request_uri'] = $this->url;
        if ($this->client === null) {
            if (class_exists(\Swoole\Coroutine\Client::class)) {
                $this->client = new \Swoole\Coroutine\Client(
                    $this->isSsl ? SWOOLE_TCP | SWOOLE_SSL : SWOOLE_TCP
                );
            } else {
                $this->client = new Client($this->isSsl);
            }
        }
        $this->client -> set($this->options);
        if (!$this->client -> connect($this->host, $this->port, 0.5)) {
            $this->error = $this->client -> errMsg;
        } else {
            $this->client -> send(json_encode($this->param, JSON_UNESCAPED_UNICODE));
            $this->data = $this->client -> recv(30);
            $this->error = $this->client -> close() ? '' : 'Close Connection Fail';
        }

        return $this;
    }

    /**
     * 获取响应内容
     * @return array|string
     */
    public function getData(): array|string {
        return json_decode($this->data, true) ?? $this->data;
    }

    /**
     * 获取异常请求
     * @return string
     */
    public function getError(): string {
        return $this->error;
    }
}