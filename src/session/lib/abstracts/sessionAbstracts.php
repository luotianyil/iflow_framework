<?php


namespace iflow\session\lib\abstracts;


use iflow\facade\Cache;
use iflow\session\lib\Session;
use iflow\Utils\basicTools;

abstract class sessionAbstracts implements Session {

    protected string $session_id = '';
    protected string $session_name;

    protected array $config;
    protected object $cache;

    public function initializer(array $config): static
    {
        // TODO: Implement initializer() method.
        $this->config = $config;
        $this->session_name = $config['session_name'] ?? 'PHPSESSIONID';
        $this->cache = Cache::store($this->config['cache_config']);
        return $this;
    }

    /**
     * 创建SessionId
     * @return string
     */
    public function makeSessionID(): string {
        // TODO: Implement makeSessionID() method.

        if ($this->session_id !== '') return $this->session_id;

        // 验证cookie是否携带SESSION
        $this->session_id = cookie($this -> session_name) ?: '';
        if ($this->session_id !== '') return $this->session_id;

        // 验证参数是否携带SESSION_ID
        $this->session_id = request() -> params($this -> session_name);
        if ($this->session_id !== '') return $this->session_id;

        // 生成新的SESSION_ID
        $host = request() -> getDomain(true);
        $ip = request() -> ip();

        $this->session_id =
            $this->config['prefix'].($ip === '127.0.0.1' ? session_create_id() : hash('sha256', "$host-$ip"));

        cookie($this->session_name, $this->session_id);
        return $this->session_id;
    }
}