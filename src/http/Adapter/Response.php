<?php


namespace iflow\http\Adapter;


use iflow\fileSystem\File;

class Response {

    public function sendFile(string $path): bool
    {
        $content = app() -> make(File::class) -> readFile($path);
        if ($content instanceof \Generator) {
            foreach ($content as $info) {
                echo $info;
            }
        }
        return $this->finish();
    }

    public function end($data): bool {
        echo $data;
        return $this->finish();
    }

    public function finish(): bool
    {
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }
        return true;
    }

    public function header($key, $value)
    {
        header($key . ':' . $value);
    }

    public function status($code): int
    {
        return http_response_code($code);
    }

    public function cookie($name, $value, int $expires, string $path = '/', string $domain = '', $secure = '', $httponly = false, $samesite = '', $priority = ''): bool
    {
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

    public function __call(string $name, array $arguments)
    {
        // TODO: Implement __call() method.
        return true;
    }

}