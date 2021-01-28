<?php

declare (strict_types = 1);

// 助手函数
use iflow\Container;
use iflow\facade\Config;

// 应用
if (!function_exists('app')) {
    function app(string $name = '', array $args = [], bool $isNew = false)
    {
        return Container::getInstance()->make($name ?: 'iflow\Container', $args, $isNew);
    }
}

// 配置
if (!function_exists('config')) {
    function config($name = '', $value = [])
    {
        if (is_array($name)) {
            return Config::set($value, $name);
        }
        return Config::get($name, $value);
    }
}

// 请求
if (!function_exists('request')) {
    function request(): \iflow\Request
    {
        return app(\iflow\Request::class);
    }
}

// 文件
if (!function_exists('files')) {
    function files($file) : mixed
    {
        return app() -> make(\iflow\fileSystem\File::class) -> create($file);
    }
}

// 响应
if (!function_exists('response')) {
    function response() : \iflow\Response
    {
        return app(\iflow\Response::class);
    }
}

// 信息
if (!function_exists('message')) {
    function message() : \iflow\Utils\Message\Message
    {
        return app() -> make(\iflow\Utils\Message\Message::class);
    }
}

if (!function_exists('sendFile')) {
    function sendFile($path, bool $isConfigRootPath = true) : bool
    {
        $path = ($isConfigRootPath ? config('app@resources.file')['rootPath'] . DIRECTORY_SEPARATOR : '') . $path;
        response() -> sendFile($path);
        return false;
    }
}

if (!function_exists('logs')) {
    function logs(string $type = 'info', string $message = '', array $content = [])
    {
        return app() -> make(\iflow\log\Log::class) -> write($type, $message, $content);
    }
}

if (!function_exists('event')) {
    function event(string $event, ...$args) {
        return app() -> make(\iflow\event\Event::class) -> runEvent($event, $args);
    }
}

// 运行目录
if (!function_exists('runtime_path')) {
    function runtime_path($path = ''): string
    {
        return app()->getRuntimePath() . ($path ? $path . DIRECTORY_SEPARATOR : $path);
    }
}


// 返回json
if (!function_exists('json')) {
    function json($data, int $code = 200, array $headers = [], array $options = []): \iflow\response\lib\Json {
        return \iflow\Response::create($data, $code, 'json')
            -> headers($headers) -> options($options);
    }
}

// 返回xml
if (!function_exists('xml')) {
    function xml(array $data, int $code = 200, array $headers = [], array $options = []): \iflow\response\lib\Xml {
        return \iflow\Response::create($data, $code, 'xml')
            -> headers($headers) -> options($options);
    }
}
