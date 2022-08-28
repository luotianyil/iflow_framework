<?php


namespace iflow\session\Adapter;

use iflow\session\Adapter\abstracts\SessionAbstracts;

class File extends SessionAbstracts
{
    /**
     * @var \iflow\cache\Adapter\File\File
     */
    protected object $cache;

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