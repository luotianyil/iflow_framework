<?php


namespace iflow\session\lib;

use iflow\session\lib\abstracts\sessionAbstracts;

class File extends sessionAbstracts
{
    /**
     * @var \iflow\cache\lib\File
     */
    protected object $cache;

    public function initializer(array $config = []): static
    {
        parent::initializer($config);
        $this->cache->gc($this->config['expired']);
        return $this;
    }

    public function get(string $name)
    {
        // TODO: Implement get() method.
        return $this->cache -> get($name);
    }

    public function delete(string $name)
    {
        // TODO: Implement delete() method.
        return $this->cache -> delete($name);
    }

    public function set(string|null $name, $default)
    {
        // TODO: Implement set() method.
        if ($name === null && count($default) === 0) return false;
        $name = !$name ? $this->makeSessionID() : $name;
        $this->cache -> set($name, $default);
        return $name;
    }
}