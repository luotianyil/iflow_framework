<?php


namespace iflow\socket\workman\http\lib;


use iflow\http\lib\Cookie;

class Request
{
    public array $server;

    public array $get = [];
    public array $post = [];
    public array $header = [];
    public array $files = [];

    public string $request_uri = "/";
    public string $request_protocol = "";
    public string $request_method = "";

    public Cookie $cookie;


    public function __construct(
        protected \Workerman\Protocols\Http\Request $request
    ) {
        // 初始化参数
        $this->get = $this->request -> get() ?: [];
        $this->post = $this->request -> post() ?: [];
        $this->header = $this->request -> header() ?: [];
        $this->request_uri = $this->request -> uri();
        $this->request_protocol = $this->request -> protocolVersion();
        $this->cookie = new Cookie($this->request -> cookie());
        $this->request_method = $this->request -> method();

        $this->initServer();
    }

    // 初始化ServerParams
    public function initServer()
    {
        $request_uri = explode("?", $this->request_uri);
        $this->server = $this->header;
        $this->server['request_method'] = $this->request_method;
        $this->server['request_uri'] = $this->request_uri;
        $this->server['path_info'] = $request_uri[0];
        $this->server['server_protocol'] = $this->request_protocol;
        $this->server['request_time'] = time();
        $this->server['request_time_float'] = $this->server['request_time'];

        //服务端监听端口
        $this->server['server_port'] = explode(':', $this->header['host'])[1];
    }

    // 获取原始请求包体
    public function getContent(): string
    {
        return $this->request -> rawBody();
    }

    public function __call(string $name, array $arguments)
    {
        // TODO: Implement __call() method.
        return call_user_func([$this->request, $name], ...$arguments);
    }
}