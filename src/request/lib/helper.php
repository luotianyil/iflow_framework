<?php


namespace iflow\request\lib;


use iflow\fileSystem\lib\upLoadFile;

trait helper
{

    public mixed $request = null;
    public array $server = [];
    public string $request_uri = '';
    public string $query_string = '';
    public string $request_method = '';

    // 获取ip信息
    protected string $realIp = '';
    protected array $proxyIpHeader = [
        'X_REAL_IP', 'X_FORWARDED_FOR',
        'CLIENT_IP', 'X_CLIENT_IP',
        'X_CLUSTER_CLIENT_IP'
    ];

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
     * 获取POST参数
     * @param string $name
     * @param string $default
     * @return mixed
     */
    public function postParams(string $name = '', string $default = ''): mixed
    {
        if (!$this->isPost()) return [];
        $row = $this->request -> getContent();
        $params = is_array($row) ? $row : (json_decode($row, true)?: $this->request -> post);
        if ($name === '') return $params;
        return $params[$name] ?? $default;
    }

    /**
     * 根据请求获取参数
     * @param string $name
     * @return mixed
     */
    public function params(string $name = ''): mixed
    {
        return match ($this->isPost()) {
            true => $this->postParams($name),
            false => $this->getParams($name)
        };
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
     * @return string|array|null
     */
    public function getHeader(string $name = ''): string|array|null
    {
        if ($name === '') return $this->request -> header;
        return $this->get(strtolower(str_replace('_', '-', $name)), 'header', '');
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

    /**
     * 获取上传文件
     * @param string $name
     * @return upLoadFile|array
     */
    public function file(string $name = ''): upLoadFile|array
    {
        $upLoadFile = app() -> make(upLoadFile::class);
        $file = $name === '' ? $upLoadFile -> getFileList() : $upLoadFile -> getFile($name);
        return $file ?: [];
    }

    /**
     * 获取请求URL
     * @return string
     */
    public function getRequestUri(): string
    {
        return sprintf("%s://%s%s?%s", ...[
            $this->isHTTPS() ? 'https' : 'http',
            $this->getDomain(),
            $this->request_uri,
            $this->query_string
        ]);
    }
}