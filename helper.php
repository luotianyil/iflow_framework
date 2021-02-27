<?php

declare (strict_types = 1);

// 助手函数
use iflow\Container;
use iflow\facade\Config;

// 应用
if (!function_exists('app')) {
    function app(string $name = '', array $args = [], bool $isNew = false)
    {
        if ($name === '')  return Container::getInstance();
        return Container::getInstance() -> make($name, $args, $isNew);
    }
}

// 配置
if (!function_exists('config')) {
    function config($name = '', $value = []): mixed
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

if (!function_exists('find_files')) {
    function find_files(string $root, \Closure $filter) {
        $items = new \FilesystemIterator($root);
        foreach ($items as $item) {
            if ($item->isDir() && !$item->isLink()) {
                yield from find_files($item->getPathname(), $filter);
            } else {
                if ($filter($item)) {
                    yield $item;
                }
            }
        }
    }
}

// 响应
if (!function_exists('response')) {
    function response() : \iflow\Response
    {
        return app(\iflow\Response::class);
    }
}

if (!function_exists('app_server')) {
    function app_server(): \Swoole\Server | \Swoole\Http\Server | \Swoole\WebSocket\Server
    {
        return app() -> make(\Swoole\Server::class);
    }
}

// 信息
if (!function_exists('message')) {
    function message($type = 'json') : \iflow\Utils\Message\Message
    {
        return app() -> make(\iflow\Utils\Message\Message::class) -> setFilter($type);
    }
}

if (!function_exists('emails')) {
    function emails(
        array $to,
        string $body = '',
        array $files = [],
        string $subject = ''
    ) {
        $content = new \iflow\Swoole\email\lib\Message\Html();
        $content = $content -> setHtml($body) -> setSubject($subject);
        foreach ($files as $file) {
            $content = $content -> addAttachment(
                $file['filename'],
                $file['filePath'],
                $file['mime']
            );
        }
        return (new \iflow\Swoole\email\Mailer()) -> setTo($to) -> send($content);
    }
}

if (!function_exists('systemInfo')) {
    function systemInfo(): array {
        return (new \iflow\Utils\Tools\SystemTools()) -> getSystemInfo();
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

// write log
if (!function_exists('logs')) {
    function logs(string $type = 'info', string $message = '', array $content = [])
    {
        return app() -> make(\iflow\log\Log::class) -> write($type, $message, $content);
    }
}

// event
if (!function_exists('event')) {
    function event(string $event, ...$args) {
        return app() -> make(\iflow\event\Event::class) -> callEvent($event, $args);
    }
}

// 运行目录
if (!function_exists('runtime_path')) {
    function runtime_path($path = ''): string
    {
        return app()->getRuntimePath() . ($path ? $path . DIRECTORY_SEPARATOR : $path);
    }
}

// appClassName
if (!function_exists('app_class_name')) {
    function app_class_name(): string
    {
        return app()->getAppClassName();
    }
}

// rpc
if (!function_exists('rpc')) {
    function rpc($clientName, $url, array &$param = []) {
        $config = config('rpc@server.clientList');
        $clientList = Config::getConfigFile($config['path'] . $config['name'])['clientList'] ?? [];
        foreach ($clientList as $key) {
            if ($key['name'] === $clientName) {
                $param['request_uri'] = $url;
                return app_server() -> send($key['fd'],
                    json_encode($param, JSON_UNESCAPED_UNICODE)
                );
            }
        }
        return null;
    }
}

// request_rpc
if (!function_exists('rpcRequest')) {
    function rpcRequest(string $host, int $port, string $url, array $param = [], array $options = []): \iflow\Swoole\Rpc\lib\rpcRequest
    {
        $res = app() -> make(
            \iflow\Swoole\Rpc\lib\rpcRequest::class,
            func_get_args(),
            isNew: true
        );
        $res -> request();
        return $res;
    }
}

if (!function_exists('httpRequest')) {
    function httpRequest(
        string $host,
        int $port = 0,
        string $method = 'GET',
        bool $isSsl = false,
        array $header = [],
        array $data = [],
        array $options = [],
        string $type = "http"
    ): \iflow\Swoole\Scrapy\http\http | \iflow\Swoole\Scrapy\http\http2
    {
        $class = $type === "http" ? \iflow\Swoole\Scrapy\http\http::class : \iflow\Swoole\Scrapy\http\http2::class;
        return app() -> make(
            $class,
            isNew: true
        ) -> process([
            'host' => $host,
            'port' => $port,
            'method' => $method,
            'data' => $data,
            'isSsl' => $isSsl,
            'header' => $header,
            'options' => $options
       ]);
    }
}

// session
if (!function_exists('session')) {
    function session(string|null $name = null, array|null $default = []) {
        $session = app() -> make(\iflow\session\Session::class) -> initializer();

        if ($default === null) {
            return $session -> delete($name);
        }

        if (count($default) > 0) {
            return $session -> set($name, $default);
        }

        return $session -> get($name);
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

if (!function_exists('view')) {
    function view() {
    }
}

if (!function_exists('hasha')) {
    function hasha($string): string {
        return md5(hash_hmac("sha512", $string, '!dJ&S6@GliG3'));
    }
}

if (!function_exists('bt_to_magnet')) {
    function bt_to_magnet($torrent) {
        return (new \iflow\Utils\torrent\Lightbenc()) -> bdecode_getinfo($torrent);
    }
}
