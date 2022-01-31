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

        $this->sessionId = $this->getSessionId();

        // 初始化 SessionTools
        $this->sessionTools = new ArrayTools($this->session -> get($this->sessionId));
        return $this;
    }

    /**
     * 获取Session
     * @param string $name
     * @return array|string
     */
    public function get(string $name = ''): array|string {
        if ($name === '') return $this->sessionTools -> all();
        return $this->sessionTools -> get($name);
    }

    /**
     * 设置Session
     * @param string|null $name
     * @param array|string $data
     * @return mixed
     */
    public function set(string|null $name = null, array|string $data = []): mixed {
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
     * 获取当前的SESSION_ID
     * @return string
     */
    public function getSessionId(): string {
        return $this->session -> makeSessionID();
    }
}