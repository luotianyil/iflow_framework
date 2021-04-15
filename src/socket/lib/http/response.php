<?php


namespace iflow\socket\lib\http;


use iflow\fileSystem\File;

class response
{

    protected array $header = [];
    protected string $body = "";

    // http 状态码
    protected array $status = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Unused',
        307 => 'Temporary Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Time-out',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Large',
        415 => 'Unsupported Media Type',
        416 => 'Requested range not satisfiable',
        417 => 'Expectation Failed',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Time-out',
        505 => 'HTTP Version not supported'
    ];

    public function __construct(
        protected $socket
    )
    {
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
    public function end($data = ""): bool
    {
        $this->body = $this->setResponseBody() . $data;
        socket_write($this->socket, $this->body, strlen($this->body));
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
     * @param string $secure
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
        $cookie = "";
        $cookie .=  "$name=$value; expires=$expires; path=$path; domain=$domain";

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
     */
    public function sendFile(string $path): bool
    {
        $content = app() -> make(File::class) -> readFile($path);
        $this->body = $this->setResponseBody();
        socket_write($this->socket, $this->body, strlen($this->body));
        if ($content instanceof \Generator) {
            foreach ($content as $info) {
                socket_write($this->socket, $info, strlen($info));
            }
        }
        return true;
    }
}