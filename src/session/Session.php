<?php


namespace iflow\session;


class Session
{
    protected string $namespace = '\\iflow\\session\\lib\\';
    protected array $config = [];
    protected ?object $session = null;

    public function initializer() {
        if ($this->session !== null) {
            return $this;
        }
        $this->config = config('session');
        if (!$this->config) throw new \Exception('session config null');
        $class = $this->namespace . ucfirst($this->config['type']);
        $this -> session = app($class) -> initializer($this->config);
        return $this;
    }

    public function get(string $name): array {
        return $this->session -> get($name) ?: [];
    }

    public function set(string|null $name = null, array $data = []) {
        return $this->session -> set($name, $data);
    }

    public function delete(string $name)
    {
        return $this->session -> delete($name);
    }
}