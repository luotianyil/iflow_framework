<?php


namespace iflow\session\lib;

use iflow\session\lib\abstracts\sessionAbstracts;

class Redis extends sessionAbstracts
{
    /**
     * @var \iflow\cache\lib\IRedis
     */
    protected object $cache;

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
                $name, $default, strtotime('+'. $this->config['expire'] . 'second')
            );
            return $name;
        }
        return  $this -> cache -> set(
            $name,
            is_string($default) ? $default : array_replace_recursive($this->get($name), $default),
            strtotime('+'. $this->config['expire'] . 'second')
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