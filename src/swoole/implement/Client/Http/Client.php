<?php

namespace iflow\swoole\implement\Client\Http;

use iflow\Scrapy\implement\Query\Client\Proxy;
use iflow\Scrapy\implement\Query\Queue;
use iflow\Scrapy\implement\Request\Request;
use iflow\Scrapy\Scrapy;

class Client {

    protected Queue $queue;

    protected Proxy $proxy;

    public function __construct(protected array $options = []) {
        $this->queue = new Queue();
        $this->proxy = new Proxy();
    }

    /**
     * 添加请求信息
     * @param Request $request
     * @param callable $cb
     * @param array $options
     * @return $this
     */
    public function addRequest(Request $request, callable $cb, array $options = []): Client {
        $this->queue -> add($request, $cb, $options);
        return $this;
    }

    /**
     * 追加代理信息
     * @param string $host
     * @param int $port
     * @param string $scheme
     * @param string $username
     * @param string $password
     * @param array $NonProxyDomain
     * @return $this
     */
    public function addProxyAddress(string $host, int $port, string $scheme = 'http', string $username = '', string $password = '', array $NonProxyDomain = []): Client {
        $this->proxy -> addProxy(...func_get_args());
        return $this;
    }

    /**
     * 发送请求
     * @return void
     */
    public function send(): void {
        (new Scrapy($this->queue, $this->proxy, options: $this->options)) -> request();
    }

}