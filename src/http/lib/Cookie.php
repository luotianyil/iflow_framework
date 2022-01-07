<?php


namespace iflow\http\lib;


class Cookie
{
    protected array $config = [
        // cookie 保存时间
        'expires'    => 0,
        // cookie 保存路径
        'path'      => '/',
        // cookie 有效域名
        'domain'    => '',
        //  cookie 启用安全传输
        'secure'    => false,
        // httponly设置
        'httponly'  => false,
        'samesite' => '',
        'priority' => ''
    ];

    public function __construct(
        protected array $cookie = []
    ) {
        $this->config = array_replace_recursive($this->config, config('cookie')) ?: $this->config;
    }

    public function get(string $name = '')
    {
        if ($name === '') return $this->cookie;
        return $this->has($name) ? $this->cookie[$name] : null;
    }

    public function has(string $name): bool
    {
        return !empty($this->cookie[$name]);
    }

    public function set(string $name, $value, array $options = []): static
    {
        if (empty($options['expires'])) {
            $this->config['expires'] = $this->config['expires'] === 0 ? 315360000 : $this->config['expires'];
            $options['expires'] = time() + intval($this -> config['expires']);
        }
        $this->cookie[$name] = $value;
        $this->saveCookie($name, $value, array_replace_recursive($this->config, $options));
        return $this;
    }

    public function del($name): static
    {
        unset($this->cookie[$name]);
        $this->saveCookie($name, '', [ 'expires' => time() - 3600 ]);
        return $this;
    }


    public function toArray(): array
    {
        return $this->cookie;
    }

    protected function saveCookie(string $name, $value, array $options = [])
    {
        if (is_array($value)) $value = serialize($value);
        response() -> response -> cookie(
            $name, $value, $options['expires'],
            $options['path'], $options['domain'], $options['secure'],
            $options['httponly'], $options['samesite'], $options['priority']
        );
    }

}