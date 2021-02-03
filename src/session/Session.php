<?php


namespace iflow\session;


class Session
{
    protected string $namespace = '\\iflow\\session\\lib\\';
    protected array $config = [];
    protected object|null $session = null;

    public function initializer() {
        if ($this->session) {
            return $this;
        }

        $this->config = config('session');
        if (!$this->config) throw new \Exception('session config null');
        $class = $this->namespace . ucfirst($this->config['type']);
        $this -> session = (new $class) -> initializer($this->config);
        return $this;
    }

    public function get(string $name) {
        return $this->session -> get($name);
    }

    public function set(string|null $name = null, array $data = []) {
        return $this->session -> set($name, $data);
    }

    public function delete(string $name)
    {
        return $this->session -> delete($name);
    }
}