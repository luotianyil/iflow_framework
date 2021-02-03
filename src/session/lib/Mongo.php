<?php


namespace iflow\session\lib;


class Mongo implements Session
{

    protected object|null $mongodb = null;
    protected array $config = [];

    public function initializer(array $config = [])
    {
        $this->config = $config;
        if (is_string($config['mongo_model'])) {
            if (! class_exists($config['mongo_model'])) throw new \Exception('session 指定模型不存在');
            $this->mongodb = new $config['mongo_model'];
        } else {
            $this->mongodb = $config['mongo_model'];
        }
    }

    public function set(string|null $name = null, array $default = []): mixed
    {

        if ($name === null) {
            if (count($default) <= 0) return false;
            return $this -> mongodb -> insert($default, true);
        }
        return $this -> mongodb -> where([
            '_id' => $name
        ]) -> update($default);
    }

    public function get(string $name): array
    {
        $session = $this->mongodb -> where([
            '_id' => $name
        ]) -> findOrEmpty();
        return $session -> isExists() ? $session : [];
    }

    public function delete(string $name)
    {
        return $this -> mongodb -> where(['_id' => $name]) -> delete();
    }

}