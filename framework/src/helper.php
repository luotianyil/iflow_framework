<?php

declare (strict_types = 1);

// 助手函数
use iflow\App;
use iflow\Container;
use iflow\facade\Config;

if (!function_exists('app')) {
    function app(string $name = '', array $args = [], bool $isNew = false)
    {
        return Container::getInstance()->make($name ?: 'iflow\Container', $args, $isNew);
    }
}

if (!function_exists('config')) {
    function config($name = '', $value = [])
    {
        if (is_array($name)) {
            return Config::set($value, $name);
        }
        return Config::get($name, $value);
    }
}

if (!function_exists('request')) {
    function request(): \iflow\Request
    {
        return app(\iflow\Request::class);
    }
}

if (!function_exists('response')) {
    function response() : \iflow\Response
    {
        return app(\iflow\Response::class);
    }
}

if (!function_exists('message')) {
    function message() : \iflow\Utils\Message\Message
    {
        return app(\iflow\Utils\Message\Message::class);
    }
}

if (!function_exists('runtime_path')) {
    function runtime_path($path = ''): string
    {
        return app()->getRuntimePath() . ($path ? $path . DIRECTORY_SEPARATOR : $path);
    }
}

if (!function_exists('file')) {
    function file($path = ''): string
    {
        return request() -> request -> files;
    }
}

// 返回json
if (!function_exists('json')) {
    function json(array $data, int $code = 200, array $headers = [], array $options = []): \iflow\response\lib\Json {
        return \iflow\Response::create($data, 'json', $code)
            -> headers($headers) -> options($options);
    }
}

// 返回xml
if (!function_exists('xml')) {
    function xml(array $data, int $code = 200, array $headers = [], array $options = []): \iflow\response\lib\Xml {
        return \iflow\Response::create($data, 'xml', $code)
            -> headers($headers) -> options($options);
    }
}
