<?php


namespace iflow\session\lib;


use iflow\facade\Cache;
use iflow\Utils\basicTools;

class Redis implements Session
{

    protected \iflow\cache\lib\Redis $redis;
    protected array $config;

    public function initializer(array $config): static
    {
        $this->config = $config;
        $this->redis = Cache::store($this->config['cache_config']);
        $this->redis -> select($this->config['redis_db_index']);
        return $this;
    }

    public function set(string|null $name = null, array $default = [])
    {
        if ($name === null) {
            if (count($default) <= 0) return false;
            $name = $this->makeSessionName();
            $this->redis -> set($name, $default, opt: [
                'ex' => strtotime('+'. $this->config['expire'] . 'second')
            ]);
            return $name;
        }
        return $this->redis -> set($name, array_replace_recursive($this->get($name), $default), opt: [
            'ex' => strtotime('+'. $this->config['expire'] . 'second')
        ]) ? $name : null;
    }

    public function get($name)
    {
        $data = $this->redis -> get($name);
        return $data ?? [];
    }

    public function delete(string $name)
    {
        return $this->redis -> delete($name);
    }

    protected function makeSessionName(): string {
        return uniqid($this->config['prefix']) . (new basicTools()) -> make_random_number();
    }
}