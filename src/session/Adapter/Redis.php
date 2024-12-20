<?php


namespace iflow\session\Adapter;

use iflow\cache\Adapter\AdapterInterface;
use iflow\session\Adapter\abstracts\SessionAbstracts;
use think\Model;

class Redis extends SessionAbstracts {

    protected AdapterInterface|Model $cache;

    public function initializer(array $config): static
    {
        parent::initializer($config);
        $this -> cache -> select($this->config['redis_db_index']);
        return $this;
    }

    public function set(string|null $name = null, array|string $default = [])
    {
        if (!$name) {
            if (count($default) <= 0) return false;
            $name = $this->makeSessionID();
            $this -> cache -> set(
                $name, $default, strtotime('+'. $this->config['expired'] . 'second')
            );
            return $name;
        }
        return $this -> cache -> set(
            $name,
            is_string($default) ? $default : array_replace_recursive($this->get($name), $default),
            strtotime('+'. $this->config['expired'] . 'second')
        ) ? $name : null;
    }

    public function get($name)
    {
        $data =  $this -> cache -> get($name);
        return $data ?: [];
    }

    public function delete(string $name): int
    {
        return  $this -> cache -> del($name);
    }
}