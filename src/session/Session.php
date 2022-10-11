<?php

namespace iflow\session;

use iflow\Helper\Arr\Arr;
use iflow\session\Adapter\abstracts\SessionAbstracts;

class Session
{
    protected string $namespace = '\\iflow\\session\\Adapter\\';
    protected array $config = [];
    protected ?SessionAbstracts $session = null;
    protected mixed $sessionId = '';

    // 存放的session信息
    protected Arr $sessionTools;

    /**
     * 初始化 Session
     * @return $this
     * @throws \Exception
     */
    public function initializer(): static {

        $this->config = config('session');
        if (!$this->config) throw new \Exception('session config null');
        $class = $this->namespace . ucfirst($this->config['type']);
        $this -> session = app($class, [], true) -> initializer($this->config);

        $this->sessionId = $this->getSessionId();

        // 初始化 SessionTools
        $this->sessionTools = new Arr($this->session -> get($this->sessionId) ?: []);
        return $this;
    }

    /**
     * 获取Session
     * @param string $name
     * @param callable|null $callable
     * @return mixed
     */
    public function get(string $name = '', ?callable $callable = null): mixed {
        $data = $name === '' ? $this->sessionTools -> all() : $this->sessionTools -> get($name);
        return $callable ? $callable($data) : $data;
    }

    /**
     * 设置Session
     * @param string|null $name
     * @param array|string $data
     * @param callable|null $callable
     * @return mixed
     */
    public function set(string|null $name = null, array|string $data = [], ?callable $callable = null): mixed {
        $save = $this->sessionTools -> offsetSet($name, $data);
        return $callable ? $callable($save, $this->sessionTools -> all()) : $save;
    }

    /**
     * 删除Session
     * @return mixed
     * @throws \Exception
     */
    public function delete(): mixed {
        return $this->session -> delete($this->getSessionId());
    }

    /**
     * 取消引用Session内某个值
     * @param string $key
     * @return bool
     * @throws \Exception
     */
    public function unsetKey(string $key): bool {
        $this->sessionTools -> offsetUnset($key);
        return true;
    }

    /**
     * 获取当前的SESSION_ID
     * @return string
     * @throws \Exception
     */
    public function getSessionId(): string {
        return $this->session -> makeSessionID();
    }

    /**
     * 保存Session至缓存
     * @return mixed
     * @throws \Exception
     */
    public function save(): mixed {

        if (!$this->session -> get($this->getSessionId()) && $this->sessionTools -> count() === 0) {
            return false;
        }

        return $this->session -> set($this->getSessionId(), $this->sessionTools -> all());
    }
}