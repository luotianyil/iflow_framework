<?php


namespace iflow\request\Adapter;


use iflow\Container\implement\generate\exceptions\InvokeClassException;
use iflow\fileSystem\implement\UploadFile;

trait Helper {

    public array $server = [];

    public mixed $request = null;

    public string $request_uri = '';

    public string $query_string = '';

    public string $request_method = '';

    // 获取ip信息
    protected string $realIp = '';

    protected array $proxyIpHeader = [
        'X_REAL_IP', 'X_FORWARDED_FOR',
        'CLIENT_IP', 'X_CLIENT_IP',
        'X_CLUSTER_CLIENT_IP', 'REMOTE_ADDR',
    ];

    /**
     * 获取当前请求地址
     * @param bool $strict
     * @return string
     */
    public function host(bool $strict = false): string
    {
        $host = $this->getHeader('host');
        if (!$host) {
            $host = strval($this->server('X_FORWARDED_HOST') ?: $this->server('HTTP_HOST'));
        }
        return $strict ? strstr($host, ':', true) : $host;
    }

    /**
     * 获取当前请求域名
     * @param bool $port 是否去除当前请求端口
     * @param bool $scheme_uri 是否显示 URI 头
     * @return string
     */
    public function getDomain(bool $port = false, bool $scheme_uri = true): string {
        $host = $this->host($port);
        if (str_starts_with('http', $host)) return $host;

        $scheme = $this -> server('request_scheme');
        $scheme = $scheme ? "$scheme://" : ($this -> isHTTPS() ? 'https://' : 'http://');
        return !$scheme_uri ? $host : $scheme.$host;
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
        return boolval(filter_var($ip, FILTER_VALIDATE_IP, $flag));
    }

    /**
     * 获取默认语言
     * @return string
     */
    public function getLanguage(): string
    {
        $language = $this->getHeader('Accept-Language');
        if ($language) return explode(',', $language)[0];
        return 'zh-CN';
    }

    /**
     * 验证参数是否存在
     * @param string $paramName
     * @param string $type
     * @return bool
     */
    public function has(string $paramName, string $type = 'get'): bool
    {
        if (!in_array($type, ['post', 'get', 'header'])) {
            return false;
        }
//        return array_key_exists($paramName, $this->request -> {$type});
        return !empty($this->request -> {$type}[$paramName]);
    }

    /**
     * 获取POST参数
     * @param string $name
     * @param string $default
     * @return mixed
     */
    public function postParams(string $name = '', mixed $default = ''): mixed
    {
        if ($this->isGet()) return [];
        $row = $this->request -> getContent();
        $params = is_array($row) ? $row : (json_decode($row, true)?: $this->request -> post);
        if ($name === '') return $params;
        return $params[$name] ?? $default;
    }

    /**
     * 获取POST参数
     * @param string $name
     * @param string $default
     * @return mixed
     */
    public function post(string $name = '', string $default = ''): mixed {
        return $this -> postParams($name, $default);
    }

    /**
     * 根据请求获取参数
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function params(string $name = '', mixed $default = ''): mixed {
        return match ($this->isGet()) {
            false => $this->postParams($name, $default),
            true => $this->getParams($name, $default)
        };
    }

    /**
     * 获取get参数
     * @param string $name
     * @param string $default
     * @return string|array|null
     */
    public function getParams(string $name = '', mixed $default = ''): mixed {
        if ($name === '') return $this->request -> get;
        return $this->get($name, 'get', $default);
    }

    /**
     * 获取头部参数
     * @param string $name
     * @return string|array|null
     */
    public function getHeader(string $name = ''): mixed {
        if ($name === '') return $this->request -> header;
        return $this->get(strtolower(str_replace('_', '-', $name)), 'header', '');
    }


    /**
     * 获取Server参数
     * @param string $name
     * @return mixed
     */
    public function server(string $name = ''): mixed {
        if ($name === '') return $this->request -> header;
        $name = strtolower($name);
        return $this->server[str_replace('_', '-', $name)] ?? ($this->server[$name] ?? null);
    }

    /**
     * 获取请求参数
     * @param string $name
     * @param string $type
     * @param string $default
     * @return string|array|null
     */
    protected function get(string $name, string $type = 'get', mixed $default = ''): mixed {
        if ($this->has($name, $type)) {
            return $this->request -> {$type}[$name] ?? $default;
        }
        return $default;
    }

    /**
     * 获取上传文件
     * @param string $name
     * @return UploadFile|UploadFile[]
     * @throws InvokeClassException
     */
    public function file(string $name = ''): UploadFile|array {
        $upLoadFile = app(UploadFile::class);
        $file = $name === '' ? $upLoadFile -> getFileList() : $upLoadFile -> getFile($name);
        return $file ?: [];
    }

    /**
     * 获取请求URL
     * @return string
     */
    public function getRequestUri(): string {
        return sprintf("%s://%s%s?%s",
            $this->isHTTPS() ? 'https' : 'http', $this->getDomain(),
            $this->request_uri, $this->query_string
        );
    }
}