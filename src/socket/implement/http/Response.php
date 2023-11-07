<?php


namespace iflow\socket\implement\http;


use iflow\Container\implement\generate\exceptions\InvokeClassException;
use iflow\fileSystem\File;
use iflow\http\ResponseStatus;

class Response {

    protected array $header = [];

    protected string $body = "";

    // http 状态码
    protected array $status = ResponseStatus::RESPONSE_STATUS;

    // 是否已经结束请求
    protected bool $isWritable = true;

    public function __construct(
        protected $socket
    ) {
        $this->header = [
            'date' => gmdate('D, d M Y H:i:s T'),
            'content-type' => 'text/html',
            'status' => "HTTP/1.1 200 OK"
        ];
    }

    /**
     * 请求结束返回响应
     * @param string $data
     * @return bool
     */
    public function end(string $data = ""): bool
    {
        $this->body = $this->setResponseBody() . $data;
        socket_write($this->socket, $this->body, strlen($this->body));
        $this->isWritable = false;
        return true;
    }

    /**
     * 设置http响应头
     * @param string $key
     * @param string|int $value
     * @return $this
     */
    public function header(string $key, string|int $value): static
    {
        $key = strtolower($key);
        if ($key === 'status') {
            return $this->status($value);
        }
        $this->header[$key] = $value;
        return $this;
    }

    /**
     * 设置http响应状态
     * @param int $code
     * @return $this
     */
    public function status(int $code = 200): static
    {
        if (isset($this->status[$code])) {
            $this->header['status'] = "HTTP/1.1 $code {$this->status[$code]}";
        }
        return $this;
    }

    /**
     * 设置返回响应包体
     * @return string
     */
    protected function setResponseBody(): string {
        // 初始化头部信息
        $header = $this->header['status']. "\r\n";
        unset($this->header['status']);

        // 设置cookie
        if (isset($this->header['set-cookie'])) {
            $setCookie = $this->header['set-cookie'];
            unset($this->header['set-cookie']);
            foreach ($setCookie as $cookie) {
                $header .= "set-cookie: ". $cookie."\r\n";
            }
        }

        // 合并剩下 头部信息
        foreach ($this->header as $key => $value) {
            $header .= "$key: $value\r\n";
        }
        $body = "\r\n";
        return $header . $body;
    }

    /**
     * 存储cookie
     * @param string $name
     * @param string $value
     * @param int $expires
     * @param string $path
     * @param string $domain
     * @param bool $secure
     * @param bool $httponly
     * @param string $samesite
     * @param string $priority
     * @return response
     */
    public function cookie(
        string $name,
        string $value,
        int $expires,
        string $path = '/',
        string $domain = '',
        bool $secure = false,
        $httponly = false,
        $samesite = '',
        $priority = ''
    ): static {
        $cookie = "$name=$value; expires=$expires; path=$path; domain=$domain";

        if ($secure) {
            $cookie .= "; Secure";
        }

        if ($httponly) {
            $cookie .= "; httpOnly";
        }

        if ($samesite !== '') {
            $cookie .= "; samesite=$samesite";
        }

        if ($priority !== '') {
            $cookie .= "; priority=$priority";
        }

        $this->header['set-cookie'][] = $cookie;

        return $this;
    }

    /**
     * 发送文件
     * @param string $path
     * @return bool
     * @throws InvokeClassException
     */
    public function sendfile(string $path): bool
    {
        $content = app(File::class) -> readFile($path);
        $this->body = $this->setResponseBody();
        socket_write($this->socket, $this->body, strlen($this->body));
        if ($content instanceof \Generator) {
            foreach ($content as $info) {
                socket_write($this->socket, $info, strlen($info));
            }
        }
        return true;
    }

    public function isWritable(): bool
    {
        return $this->isWritable;
    }

    public function __call(string $name, array $arguments)
    {
        // TODO: Implement __call() method.
        return null;
    }
}