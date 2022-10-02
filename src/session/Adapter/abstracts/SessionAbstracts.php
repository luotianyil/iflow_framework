<?php


namespace iflow\session\Adapter\abstracts;


use iflow\cache\Adapter\AdapterInterface;
use iflow\facade\Cache;
use iflow\session\Adapter\Session;
use think\Model;

abstract class SessionAbstracts implements Session {

    protected string $session_id = '';
    protected string $session_name;

    protected array $config;
    protected AdapterInterface|Model $cache;

    protected array $getSessionIdMethod = [];

    public function initializer(array $config): static {
        // TODO: Implement initializer() method.
        $this->config = $config;
        $this->session_name = $config['session_name'] ?? 'PHPSESSIONID';
        $this->cache = Cache::store($this->config['cache_config']);

        $this -> getSessionIdMethod = [
            'cookie' => fn(string $sessionName) => cookie($sessionName) ?: '',
            'query_parameter' => fn(string $sessionName) => request() -> params($sessionName) ?: '',
            'header' => fn(string $sessionName) => request() -> getHeader($sessionName) ?: '',
        ];
        return $this;
    }

    /**
     * 创建SessionId
     * @return string
     * @throws \Exception
     */
    public function makeSessionID(): string {
        // TODO: Implement makeSessionID() method.

        if ($this->session_id !== '') return $this->session_id;

        if (empty($this->config['get_sessionId_method'])) {
            foreach ($this -> getSessionIdMethod as $method) {
                $this->session_id = $method($this->session_name);
                if ($this->session_id) return $this->session_id;
            }
        } else {
            // 依照配置指定方法获取 SessionId
            if (empty($this -> getSessionIdMethod[$this->config['get_sessionId_method']]))
                throw new \Exception('Get SessionMethod doesNot Exists');
            $this->session_id = $this -> getSessionIdMethod[$this->config['get_sessionId_method']]($this->session_name);
            if ($this->session_id) return $this->session_id;
        }

        // 生成新的SESSION_ID
        $host = request() -> getDomain(true);
        $ip = request() -> ip();

        $this->session_id = sprintf("%s%s",
            $this->config['prefix'] ?? '',
            $ip === '127.0.0.1' ? session_create_id() : hash('sha256', "$host-$ip")
        );

        cookie($this->session_name, $this->session_id);
        return $this->session_id;
    }
}