<?php


namespace iflow\session\lib;


use iflow\session\lib\abstracts\sessionAbstracts;
use think\Model;

class Mongo extends sessionAbstracts
{

    /**
     * @var Model
     */
    protected object $cache;

    public function initializer(array $config = []): static
    {
        $this->config = $config;
        if (is_string($config['mongo_model'])) {
            if (! class_exists($config['mongo_model'])) throw new \Exception('session 指定模型不存在');
            $this->cache = new $config['mongo_model'];
        } else {
            $this->cache = $config['mongo_model'];
        }

        return $this;
    }

    public function set(string|null $name = null, array $default = []): mixed
    {
        if ($name === null) {
            if (count($default) <= 0) return false;
            return $this -> cache -> insert($default, true);
        }
        return $this -> cache -> where([
            '_id' => $name
        ]) -> update($default);
    }

    public function get(string $name): array
    {
        $session = $this->cache -> where([
            '_id' => $name
        ]) -> findOrEmpty();
        return $session -> isExists() ? $session : [];
    }

    public function delete(string $name): bool
    {
        return $this -> cache -> where(['_id' => $name]) -> delete();
    }

}