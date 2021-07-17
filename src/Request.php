<?php


namespace iflow;

use iflow\fileSystem\lib\upLoadFile;

class Request
{

    public mixed $request = null;
    public array $server = [];
    public string $request_uri = '';
    public string $query_string = '';
    public string $request_method = '';

    protected string $realIp = '';

    protected array $proxyIpHeader = [
        'X_REAL_IP', 'X_FORWARDED_FOR',
        'CLIENT_IP', 'X_CLIENT_IP',
        'X_CLUSTER_CLIENT_IP'
    ];

    public function initializer($request): static
    {
        $this->request = $request;
        $this->server = $request -> server;
        $this->request_uri =
            str_replace("//", "/", explode('?', $request -> server['path_info'] ?? $request -> server['request_uri'])[0]);
        $this->query_string = $request -> server['query_string'] ?? '';
        $this->request_method = $request -> server['request_method'];

        return $this->initFile();
    }

    /**
     * 初始化上传文件
     * @return $this
     */
    protected function initFile(): static
    {
        $files = $this->request -> files ?? [];
        $upLoadFile = app() -> make(upLoadFile::class);
        foreach ($files as $key => $value) {
            $upLoadFile -> setFile($key, $value);
        }

        return $this;
    }

    /**
     * 验证参数是否存在
     * @param $param
     * @param string $type
     * @return bool
     */
    public function has($param, $type = 'get'): bool
    {
        if (!in_array($type, ['post', 'get', 'header'])) {
            return false;
        }
        return !empty($this->request -> {$type}[$param]);
    }

    /**
     * 获取get参数
     * @param string $name
     * @param string $default
     * @return string|array|null
     */
    public function getParams(string $name = '', string $default = ''): string|array|null
    {
        if ($name === '') return $this->request -> get;
        return $this->get($name, 'get', $default);
    }

    /**
     * 获取头部参数
     * @param string $name
     * @param string $default
     * @return string|array|null
     */
    public function getHeader(string $name = '', string $default = ''): string|array|null
    {
        if ($name === '') return $this->request -> header;
        return $this->get(strtolower(str_replace('_', '-', $name)), 'header', $default);
    }

    /**
     * 获取请求参数
     * @param string $name
     * @param string $type
     * @param string $default
     * @return string|array|null
     */
    protected function get(string $name, string $type, string $default = ''): string|array|null
    {
        if ($this->has($name, $type)) {
            return $this->request -> {$type}[$name] ?? $default;
        }
        return $default;
    }

    public function file(string $name = ''): upLoadFile|array
    {
        $upLoadFile = app() -> make(upLoadFile::class);
        $file = $name === '' ? $upLoadFile -> getFileList() : $upLoadFile -> getFile($name);
        return $file ?: [];
    }

    public function postParams(string $name = '', string $default = '')
    {
        if (!$this->isPost()) return [];
        $row = $this->request -> getContent();
        $params = is_array($row) ? $row : (json_decode($row, true)?: $this->request -> post);
        if ($name === '') return $params;
        return $params[$name] ?? $default;
    }

    public function params(string $name = '')
    {
        return match ($this->isPost()) {
            true => $this->postParams($name),
            false => $this->getParams($name)
        };
    }

    /**
     * 是否为POST
     * @return bool
     */
    public function isPost(): bool
    {
        return strtoupper($this->request_method) === 'POST';
    }

    /**
     * 是否为GET
     * @return bool
     */
    public function isGet(): bool
    {
        return strtoupper($this->request_method) === 'GET';
    }

    /**
     * 是否为PUT
     * @return bool
     */
    public function isPut(): bool
    {
        return strtoupper($this->request_method) === 'PUT';
    }

    /**
     * 是否为DELETE
     * @return bool
     */
    public function isDelete(): bool
    {
        return strtoupper($this->request_method) === 'DELETE';
    }

    /**
     * 是否为OPTIONS
     * @return bool
     */
    public function isOptions(): bool
    {
        return strtoupper($this->request_method) === 'OPTIONS';
    }

    /**
     * 是否为AJAX
     * @return bool
     */
    public function isAjax(): bool
    {
        $value = $this->getHeader('HTTP_X_REQUESTED_WITH') ?: $this->getHeader('X-Requested-With');
        return $value && 'xmlhttprequest' == strtolower($value);
    }

    public function getLanguage(): string
    {
        return explode(',', $this->getHeader('Accept-Language'))[0];
    }

    /**
     * 获取Host
     * @return string
     */
    public function getDomain(): string
    {
        return $this->getHeader('host');
    }

    /**
     * 获取ip 地址
     * @return string
     */
    public function ip(): string
    {
        if (!empty($this->realIp)) return $this->realIp;
        foreach ($this->proxyIpHeader as $proxyHeader) {
            $this->realIp = (string) $this->getHeader($proxyHeader);
            // 验证IP类型 如果通过退出
            if ($this->validIP($this->realIp) || $this->validIP($this->realIp, 'ipv6')) {
                break;
            }
        }

        if (!$this->realIp) $this->realIp = '127.0.0.1';
        return $this->realIp;
    }

    /**
     * 验证ip格式
     * @param string $ip
     * @param string $type
     * @return bool
     */
    public function validIP(string $ip, string $type = 'ipv4'): bool
    {
        if (!$ip) return false;
        $flag = strtolower($type) === 'ipv4' ? FILTER_FLAG_IPV4 : FILTER_FLAG_IPV6;
        return boolval(filter_var($ip, $flag));
    }

    public function __call(string $name, array $arguments)
    {
        // TODO: Implement __call() method.
        return call_user_func([$this->request, $name], ...$arguments);
    }
}