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
    function find_files(string $root, \Closure $filter): Generator | null
    {
        if (!file_exists($root)) return null;
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
    function app_server(): \Swoole\Server | \Swoole\Http\Server | \Swoole\WebSocket\Server | \Swoole\Coroutine\Client
    {
        return app() -> make(\Swoole\Server::class);
    }
}

if (!function_exists('app_client')) {
    function app_client(): \Swoole\Coroutine\Client
    {
        return app() -> make(\Swoole\Coroutine\Client::class);
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
        $config = config('rpc');
        $param['request_uri'] = $url;
        $config['server']['enable'] = $config['server']['enable'] ?? false;
        if ($config['server']['enable']) {
            $config = $config['server']['clientList'];
            $clientList = Config::getConfigFile($config['path'] . $config['name'])['clientList'] ?? [];
            foreach ($clientList as $key) {
                if ($key['name'] === $clientName) {
                    return app_server() -> send($key['fd'],
                        json_encode($param, JSON_UNESCAPED_UNICODE)
                    );
                }
            }
        } else {
            $param['client_name'] = $clientName;
            $param['isClientConnection'] = true;
            $client = app_client();
            $client  -> send(
                json_encode($param, JSON_UNESCAPED_UNICODE)
            );
            return $client -> recv(30);
        }
        return null;
    }
}

// request_rpc
if (!function_exists('rpcRequest')) {
    function rpcRequest(
        string $host,
        int $port,
        string $url,
        bool $isSsl = false,
        array $param = [],
        array $options = []
    ): \iflow\Swoole\Rpc\lib\rpcRequest
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
        array|string $data = [],
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

// cookie
if (!function_exists('cookie')) {
    function cookie(string $name = '', $value = '', array $options = []) {
        $cookie = app() -> make(\iflow\http\lib\Cookie::class);
        if ($value === '') {
            return $cookie -> get($name);
        }
        if ($value === null) return $cookie -> del($name);
        return $cookie -> set($name, $value, $options);
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

if (!function_exists('sendFile')) {
    function sendFile(string $path, int $code = 200, array $headers = [], bool $isConfigRootPath = true) : \iflow\response\lib\File
    {
        $path = ($isConfigRootPath ? config('app@resources.file.rootPath') . DIRECTORY_SEPARATOR : '') . $path;
        return \iflow\Response::create($path, $code, 'file')
            -> headers($headers);
    }
}

if (!function_exists('view')) {
    function view() {
    }
}

if (!function_exists('hasha')) {
    function hasha($string, string $key = '@QU8LP!90YB'): string {
        return md5(hash_hmac("sha512", $string, $key));
    }
}

if (!function_exists('bt_to_magnet')) {
    function bt_to_magnet($torrent) {
        return (new \iflow\Utils\torrent\Lightbenc()) -> bdecode_getinfo($torrent);
    }
}

if (!function_exists('is_cli')) {
    function is_cli(): bool {
        return (new \iflow\Utils\Tools\SystemTools()) -> isCli();
    }
}

if (!function_exists('swoole_success')) {
    function swoole_success(): bool {
        return is_cli() && extension_loaded('swoole');
    }
}


if (!function_exists('go')) {
    function go(\Closure $closure) {
        return call_user_func($closure);
    }
}

if (!function_exists('php_run_path')) {
    function php_run_path(): string {
        if(str_contains(PHP_OS, 'WIN')){
            $ini= ini_get_all();
            $path = $ini['extension_dir']['local_value'];
            $b= substr($path,0,-3);
            $php_path = str_replace('\\','/',$b);
            return $php_path.'php.exe';
        }
        return PHP_BINDIR . DIRECTORY_SEPARATOR . 'php';
    }
}

if (!function_exists('array_multi_to_one')) {
    function array_multi_to_one($array, &$arr, ?\Closure $closure = null) {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                array_multi_to_one($value, $arr, $closure);
            } else {
                if ($closure === null) $arr[] = $value;
                else if (call_user_func($closure, $value)) {
                    $arr[] = $value;
                }
            }
        }
    }
}
