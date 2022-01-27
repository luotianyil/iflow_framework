<?php

declare (strict_types = 1);

// 助手函数
use iflow\console\Console;
use iflow\Container\Container;
use iflow\EMailer\implement\Exception\MailerException;
use iflow\EMailer\implement\Message\Html;
use iflow\EMailer\Mailer;
use iflow\event\Event;
use iflow\facade\Config;
use iflow\facade\Session;
use iflow\fileSystem\File;
use iflow\i18n\I18n;
use iflow\log\Log;
use iflow\Request;
use iflow\Response;
use iflow\Swoole\Rpc\lib\rpcRequest;
use iflow\Swoole\Scrapy\http\http;
use iflow\Swoole\Scrapy\http\http2;
use iflow\template\View;
use iflow\Utils\Message\Message;
use iflow\Utils\Tools\SystemTools;
use iflow\Utils\torrent\Lightbenc;
use iflow\validate\Validate;
use React\Promise\Promise;
use Swoole\Coroutine\Client;

// 应用
if (!function_exists('app')) {
    function app(string $name = '', array $args = [], bool $isNew = false, ?callable $call = null): object {
        if ($name === '')  return Container::getInstance() -> get('iflow\\App');
        return Container::getInstance() -> make($name, $args, $isNew, $call);
    }
}

// 配置
if (!function_exists('config')) {
    function config(mixed $name = '', mixed $value = [], ?callable $call = null): mixed {
        $config = !is_string($name) ? Config::set($value, $name) : Config::get($name, $value);
        return $call ? $call($config) : $config;
    }
}

if (!function_exists('loadConfigFile')) {
    /**
     * 加载配置文件
     * @param string $file
     * @return mixed
     */
    function loadConfigFile(string $file): mixed
    {
        $type = pathinfo($file, PATHINFO_EXTENSION);
        $config = match ($type) {
            'php' => include $file,
            'ini', 'env' =>
                parse_ini_file($file, true, INI_SCANNER_TYPED) ?: [],
            'json' =>
                json_decode(file_get_contents($file), true),
            'yaml' => fn () : array =>
                function_exists('yaml_parse_file') ? yaml_parse_file($file) : [],
            default => []
        };
        $config = is_numeric($config) ? [] : $config;
        return is_object($config) ? call_user_func($config) : $config;
    }
}

// 请求
if (!function_exists('request')) {
    /**
     * @return Request
     */
    function request(): object {
        return app(Request::class);
    }
}

// 文件
if (!function_exists('local_file')) {
    function local_file($file) : mixed
    {
        return app() -> make(File::class) -> create($file);
    }
}

// 查找文件
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
    /**
     * @return Response
     */
    function response() : object {
        return app(Response::class);
    }
}

// 路由信息
if (!function_exists('router')) {
    function router() : array {
        return request() -> getRouter();
    }
}

if (!function_exists('app_server')) {
    function app_server(): \Swoole\Server | \Swoole\Http\Server | \Swoole\WebSocket\Server | Client
    {
        return app() -> make(\Swoole\Server::class);
    }
}

if (!function_exists('app_client')) {
    function app_client(): Client
    {
        return app() -> make(Client::class);
    }
}

// 信息
if (!function_exists('message')) {
    function message($type = 'json') : Message
    {
        return app() -> make(Message::class) -> setFilter($type);
    }
}

// 发送邮件
if (!function_exists('emails')) {
    function emails(
        array $to,
        string $body = '',
        array $files = [],
        string $subject = ''
    ): bool|string {
        try {
            if (count($to) < 1) return false;
            $content = new Html();
            $content = $content -> setHtml($body) -> setSubject($subject);
            foreach ($files as $file) {
                $content = $content -> addAttachment($file['filename'], $file['filePath'], $file['mime'] ?? '');
            }
            return (new Mailer(config('email', []), 'qqMailer')) -> setTo($to) -> send($content);
        } catch (MailerException $exception) {
            return $exception -> getMessage();
        }
    }
}

// 获取系统信息 仅支持 linux
if (!function_exists('systemInfo')) {
    function systemInfo(): array {
        return (new SystemTools()) -> getSystemInfo();
    }
}

// write log
if (!function_exists('logs')) {
    function logs(string $type = 'info', string $message = '', array $content = []) {
        return app() -> make(Log::class) -> write($type, $message, $content);
    }
}

// event
if (!function_exists('event')) {
    function event(string $event, ...$args) {
        return app() -> make(Event::class) -> trigger($event, $args);
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
    function rpc($clientName, $url, array $param = []) {
        $config = config('swoole.rpc');
        $param['request_uri'] = $url;
        $config['server']['enable'] = $config['server']['enable'] ?? false;
        try {
            $config = $config['server']['clientList'];
            $clientList = \iflow\facade\Cache::store($config) -> get($config['cacheName']);
            foreach ($clientList as $clientValue) {
                $clientValue = array_values($clientValue)[0];
                if ($clientValue['name'] === $clientName) {
                    return app_server() -> send($clientValue['fd'],
                        json_encode($param, JSON_UNESCAPED_UNICODE)
                    );
                }
            }
        } catch (Throwable) {
            $param['client_name'] = $clientName;
            $param['isClientConnection'] = true;
            $client = app_client();
            $client -> send(
                json_encode($param, JSON_UNESCAPED_UNICODE)
            );
            return $client -> recv(30);
        }
        return false;
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
    ): rpcRequest {
        $res = app() -> make(rpcRequest::class, func_get_args(), isNew: true);
        $res -> request();
        return $res;
    }
}

// 发送 http请求
if (!function_exists('httpRequest')) {
    function httpRequest(
        string $host,
        int $port = 0,
        string $method = 'GET',
        bool $isSsl = false,
        array $header = [],
        array|string $data = [],
        array $options = [
            'timeout' => 30
        ],
        string $type = "http"
    ): http | http2
    {
        $class = $type === "http" ? http::class : http2::class;
        return app() -> make($class, isNew: true) -> process([
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
    function session(string|null $name = null, array|string|null $default = []) {
        if ($default === null) {
            return Session::unsetKey($name);
        }
        if (is_string($default) || count($default) > 0) {
            return Session::set($name, $default);
        }
        return Session::get($name ?: '');
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
        return Response::create($data, $code, 'json')
            -> headers($headers) -> options($options);
    }
}

// 返回xml
if (!function_exists('xml')) {
    function xml(array $data, int $code = 200, array $headers = [], array $options = []): \iflow\response\lib\Xml {
        return Response::create($data, $code, 'xml')
            -> headers($headers) -> options($options);
    }
}

// 发送文件
if (!function_exists('sendFile')) {
    function sendFile(string $path, int $code = 200, array $headers = [], bool $isConfigRootPath = true) : \iflow\response\lib\File
    {
        $path = ($isConfigRootPath ? config('app@resources.file.rootPath') . DIRECTORY_SEPARATOR : '') . $path;
        return Response::create($path, $code, 'file')
            -> headers($headers);
    }
}

// 返回视图文件
if (!function_exists('view')) {
    function view(string $template, array $data = [], array $config = []): Response {
        return (new View()) -> fetch($template, $data, $config);
    }
}

// 哈希加盐
if (!function_exists('hasha')) {
    function hasha($string, string $key = '@QU8LP!90YB'): string {
        return md5(hash_hmac("sha512", $string, $key));
    }
}

// 种子文件解析
if (!function_exists('bt_to_magnet')) {
    function bt_to_magnet($torrent) {
        return (new Lightbenc()) -> bdecode_getinfo($torrent);
    }
}

// 验证是否为cli模式
if (!function_exists('is_cli')) {
    function is_cli(): bool {
        return (new SystemTools()) -> isCli();
    }
}

// 验证是否为swoole cli模式
if (!function_exists('swoole_success')) {
    function swoole_success(): bool {
        return is_cli() && extension_loaded('swoole');
    }
}

// 验证是否为HTTP服务模式
if (!function_exists('is_http_services')) {
    function is_http_services(): bool {
        if (is_cli() && request() -> getVersion() !== '') {
            return true;
        }
        return request() -> getVersion() !== '';
    }
}

// 部分代码使用 go 所以为了兼容 未安装swoole 扩展 提供此方法
if (!function_exists('go')) {
    function go(\Closure $closure, callable $onFulfilled = null, callable $onRejected = null, callable $onProgress = null) {
        $promise = new Promise($closure);
        return $promise -> then($onFulfilled, $onRejected, $onProgress) -> done();
    }
}

// 获取php可执行文件目录
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

// 数组转一维数组
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

// i18n国际化
if (!function_exists('i18n')) {
    function i18n(string $key, string|array $default = '', string $lan = ''): string {
        return app(I18n::class) -> i18n($key, $default, $lan);
    }
}

// 验证器
if (!function_exists('validate')) {
    function validate(array $rule = [], array $data = [], array $message = []) {
        $validate = new Validate();
        $error = $validate
                    -> rule($rule, $message)
                    -> check($data)
                    -> findError();

        if ($error !== null) {
            throw new Exception($error);
        }
        return null;
    }
}

if (!function_exists('dump')) {
    function dump(...$args): bool {
        ob_start();
        var_dump(...$args);
        $output = ob_get_clean();

        $output = preg_replace('/\]\=\>\n(\s+)/m', '] => ', $output);
        $outConsole = app(Console::class) -> outPut ?: null;
        var_dump($outConsole);
        if (!is_http_services() && $outConsole !== null) {
            $outConsole -> write(PHP_EOL . $output . PHP_EOL);
        } else {
            if (!extension_loaded('xdebug')) {
                $output = htmlspecialchars($output, ENT_SUBSTITUTE);
            }
            response() -> data('<pre>' . $output . '</pre>') -> send();
        }
        return true;
    }
}

if (!function_exists('valid_closure')) {
    /**
     * 验证方法是否存在
     * @param string|Closure $closure
     * @param array $args
     * @return ?Closure
     */
    function valid_closure(string|\Closure $closure, array $args = []): ?\Closure {

        if ($closure instanceof Closure || function_exists($closure)) return fn() => app() -> invoke($closure, $args);

        // 验证是否为类
        $closure = explode('@', $closure);
        if (count($closure) < 2 || !class_exists($closure[0])) return null;

        $object = app() -> make($closure[0]) ;
        if (!method_exists($object, $closure[1])) return null;

        // 执行方法闭包
        return fn() => app() -> invokeMethod([$object, $closure[1]], [...func_get_args(), $object]);
    }
}
