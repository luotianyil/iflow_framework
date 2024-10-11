<?php


namespace iflow\socket\workman\http\implement;


use iflow\http\Adapter\Cookie;
use Workerman\Protocols\Http\Request as WorkerManRequest;

class Request
{
    public array $server;

    public array $get = [];

    public array $post = [];

    public array $header = [];

    public array $files = [];

    public string $request_uri = '/';

    public string $request_protocol = '';

    public string $request_method = '';

    public Cookie $cookie;

    public function __construct(
        protected WorkerManRequest $request
    ) {
        // 初始化参数
        $this->get = $this->request -> get() ?: [];
        $this->post = $this->request -> post() ?: [];
        $this->header = $this->request -> header() ?: [];
        $this->request_uri = $this->request -> uri();
        $this->request_protocol = $this->request -> protocolVersion();
        $this->cookie = app(Cookie::class, [ $this->request -> cookie() ], true);
        $this->request_method = $this->request -> method();

        $this->initializer();
    }

    /**
     * 初始化ServerParams
     * @return void
     */
    public function initializer(): void {

        $this->server = $this -> header;

        $this->server['request_method'] = $this->request_method;
        $this->server['request_uri'] = $this->request_uri;
        $this->server['path_info'] = $this->request -> path();
        $this->server['server_protocol'] = $this->request_protocol;
        $this->server['request_time'] = time();
        $this->server['request_time_float'] = $this->server['request_time'];

        // 服务端监听端口
        $this->server['server_port'] = explode(':', $this->header['host'])[1];
    }

    /**
     * 获取原始请求包体
     * @return string
     */
    public function getContent(): string {
        return $this->request -> rawBody();
    }

    /**
     * 获取请求方法
     * @return string
     */
    public function getMethod(): string {
        return $this->request -> method();
    }

    public function __call(string $name, array $arguments) {
        // TODO: Implement __call() method.
        return call_user_func([$this->request, $name], ...$arguments);
    }
}