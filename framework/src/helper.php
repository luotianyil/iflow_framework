<?php

declare (strict_types = 1);

// 助手函数
use iflow\App;
use iflow\Container;
use iflow\facade\Config;

if (!function_exists('json')) {
    function json(array $data, int $code = 200, array $header = []) {
        echo 123;
    }
}

// 返回xml
if (!function_exists('xml')) {
    function xml(array $data, int $code = 200, array $header = []) {
    }
}

if (!function_exists('app')) {
    function app(string $name = '', array $args = [], bool $isNew = false)
    {
        return Container::getInstance()->make($name ?: App::class, $args, $isNew);
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
