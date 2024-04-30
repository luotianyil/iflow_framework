<?php

namespace iflow\http\Adapter;

use iflow\fileSystem\File;
use iflow\Container\implement\generate\exceptions\InvokeClassException;

class Response {

    /**
     * 发送文件
     * @param string $path
     * @return bool
     * @throws InvokeClassException
     */
    public function sendFile(string $path): bool {
        $content = app(File::class) -> readFile($path);
        if ($content instanceof \Generator) {
            foreach ($content as $info) echo $info;
        }
        return $this->finish();
    }

    public function write(mixed $content): bool {
        echo $content;
        return $this->finish();
    }

    /**
     * 结束请求
     * @param $data
     * @return bool
     */
    public function end($data): bool {
        return $this->write($data);
    }

    /**
     * 结束请求
     * @return bool
     */
    public function finish(): bool {
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }
        return true;
    }

    /**
     * 设置响应头
     * @param string $key
     * @param string $value
     * @return void
     */
    public function header(string $key, string $value): void {
        header($key . ':' . $value);
    }

    /**
     * 设置HTTP状态
     * @param int $code HTTP_Status 状态码
     * @return int
     */
    public function status(int $code): int {
        return http_response_code($code);
    }

    /**
     * 设置Cookie
     * @param string $name
     * @param string $value
     * @param int $expires
     * @param string $path
     * @param string $domain
     * @param string $secure
     * @param bool $httponly
     * @param string $samesite
     * @param string $priority
     * @return bool
     */
    public function cookie(string $name, string $value, int $expires, string $path = '/', string $domain = '', string $secure = '', bool $httponly = false, string $samesite = '', string $priority = ''): bool {
        return setcookie($name, $value, [
            'expires' => $expires,
            'path' => $path,
            'domain' => $domain,
            'secure' => $secure,
            'httponly' => $httponly,
            'samesite' => $samesite
        ]);
    }

    /**
     * 检测HTTP 是否已经发送
     * @return bool
     */
    public function isWritable(): bool {
        return !headers_sent();
    }

    public function __call(string $name, array $arguments) {
        // TODO: Implement __call() method.
        return true;
    }

}