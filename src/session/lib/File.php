<?php


namespace iflow\session\lib;


use iflow\facade\Cache;
use iflow\Utils\basicTools;

class File implements Session
{

    protected array $config = [];
    protected \iflow\cache\lib\File $file;

    public function initializer(array $config = []): static
    {
        $this->config = $config;
        $this->file = Cache::store($this->config['cache_config']);
        $this->file->gc($this->config['expired']);
        return $this;
    }

    public function get(string $name)
    {
        // TODO: Implement get() method.
        return $this->file -> get($name);
    }

    public function delete(string $name)
    {
        // TODO: Implement delete() method.
        return $this->file -> delete($name);
    }

    public function set(string|null $name, $default)
    {
        // TODO: Implement set() method.
        if ($name === null && count($default) === 0) return false;
        $name = $name === null ? $this->makeSessionName() : $name;
        $this->file -> set($name, $default);
        return $name;
    }

    protected function makeSessionName(): string {
        $number = (new basicTools()) -> make_random_number();
        $host = request() -> getHeader('host');
        $ip = request() -> ip();
        return uniqid($this->config['prefix']) . hash(
            'sha256', "${number}-${host}-${ip}"
            );
    }
}