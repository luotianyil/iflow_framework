<?php


namespace iflow\session;


use iflow\session\lib\abstracts\sessionAbstracts;
use iflow\Utils\ArrayTools;

class Session
{
    protected string $namespace = '\\iflow\\session\\lib\\';
    protected array $config = [];
    protected ?sessionAbstracts $session = null;
    protected mixed $sessionId = '';
    protected mixed $sessionName = '';

    // 存放的session信息
    protected ArrayTools $sessionTools;

    public function initializer(): static {
        if ($this->session !== null) {
            return $this;
        }
        $this->config = config('session');
        if (!$this->config) throw new \Exception('session config null');
        $class = $this->namespace . ucfirst($this->config['type']);
        $this -> session = app($class) -> initializer($this->config);

        $this->sessionName = $this->config['session_name'] ?? 'PHPSESSIONID';
        $this->sessionId = $this->getSessionId();

        // 初始化 SessionTools
        $this->sessionTools = new ArrayTools($this->sessionId ? $this->session -> get($this->sessionId): []);

        return $this;
    }

    public function get(string $name = ''): array|string
    {
        if ($name === '') return $this->sessionTools -> all();
        return $this->sessionTools -> get($name);
    }

    /**
     * @param string|null $name
     * @param array|string $data
     * @return mixed
     */
    public function set(string|null $name = null, array|string $data = []) {
        $this->sessionTools -> offsetSet($name, $data);
        return $this->session -> set($this->getSessionId(), $this->sessionTools -> all());
    }

    /**
     * 删除Session
     * @return mixed
     */
    public function delete(): mixed {
        return $this->session -> delete($this->getSessionId());
    }

    /**
     * 取消引用Session内某个值
     * @param string $key
     * @return bool
     */
    public function unsetKey(string $key): bool {
        $this->sessionTools -> offsetUnset($key);
        return $this->session -> set($this->getSessionId(), $this->sessionTools -> all());
    }

    /**
     * @return mixed
     */
    public function getSessionId(): mixed
    {
        if ($this->sessionId) return $this->sessionId;

        // 获取客户端传递的SessionId
        if ($this->sessionId === '') {
            // 通过 请求参数或者 请求头 获取sessionId
            $this->sessionId = request() -> params($this->sessionName);

            // 通过请求头或者cookie获取sessionId
            $this->sessionId = $this->sessionId ?: request() -> getHeader($this->sessionName);
            $this->sessionId = $this->sessionId ?: cookie($this->sessionName);
        }

        // 如果客户端未传递SessionId 即生成新的SessionId
        if (!$this->sessionId) {
            $this->sessionId = $this->session -> set(null, ['sessionName' => $this->sessionName]);
            // 将sessionId 写入cookie
            cookie($this->sessionName, $this->sessionId);
        }
        return $this->sessionId;
    }
}