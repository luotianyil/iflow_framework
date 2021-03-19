<?php


namespace iflow\http\lib;


class Response
{

    public function sendFile($path): bool
    {
        if (file_exists($path)) return $this->end(file_get_contents($path));
        return $this->end('404 - notFond');

    }

    public function end($data): bool
    {
        echo $data;
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

    public function rawCookie($name, $value, int $expires, string $path = '/', string $domain = '', $secure = '', $httponly = false, $samesite = ''): bool
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

    public function __call(string $name, array $arguments)
    {
        // TODO: Implement __call() method.
    }

}