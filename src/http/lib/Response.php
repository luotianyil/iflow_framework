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

    public function __call(string $name, array $arguments)
    {
        // TODO: Implement __call() method.
    }

}