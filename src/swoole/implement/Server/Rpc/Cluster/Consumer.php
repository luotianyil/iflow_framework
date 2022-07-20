<?php

namespace iflow\swoole\implement\Server\Rpc\Cluster;

class Consumer {

    protected Cache $cache;

    public function __construct(protected array $config) {
        $this->cache = new Cache($this->config);
    }


    public function register(array $data): bool {
        return $this->cache::add($data['fd'], $data);
    }


    public function get(int $fd): array {
        return $this->cache::get($fd);
    }

    public function getByName(string $name): array {
        return $this->cache::getConsumerByName($name);
    }


    public function remove(int $fd): bool {
        return $this->cache::del($fd);
    }


}