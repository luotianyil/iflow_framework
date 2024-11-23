<?php


namespace iflow\session\Adapter;

use iflow\cache\Adapter\AdapterInterface;
use iflow\session\Adapter\abstracts\SessionAbstracts;
use think\Model;

class File extends SessionAbstracts
{

    protected AdapterInterface|Model $cache;

    public function initializer(array $config = []): static
    {
        parent::initializer($config);
        $this->cache->gc($this->config['expired']);
        return $this;
    }

    public function get(string $name) {
        // TODO: Implement get() method.
        return $this->cache -> get($name);
    }

    public function delete(string $name): bool {
        // TODO: Implement delete() method.
        return $this->cache -> delete($name);
    }

    public function set(string|null $name, $default): bool|string {
        // TODO: Implement set() method.
        if ($name === null && count($default) === 0) return false;
        $name = !$name ? $this->makeSessionID() : $name;
        $this->cache -> set($name, $default);
        return $name;
    }
}