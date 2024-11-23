<?php

namespace iflow\swoole\implement\Tools\Pool;

use iflow\swoole\implement\Tools\Pool\Interfaces\PoolInterface;

class PoolContainer {

    /**
     * @var PoolInterface[]
     */
    protected array $pool = [];

    /**
     * 新增
     * @param string $name
     * @param PoolInterface $pool
     * @return PoolContainer
     */
    public function register(string $name, PoolInterface $pool): PoolContainer {
        $this->pool[$name] = $pool -> initializer();
        return $this;
    }


    /**
     * 销毁
     * @param string $name
     * @return mixed
     */
    public function destroy(string $name): mixed {
        $result = $this -> pool[$name] ?-> destroy();
        unset($this->pool[$name]);
        return $result;
    }

    /**
     * 销毁全部
     * @param callable|null $callable
     * @return void
     */
    public function destroyAll(?callable $callable = null): void {
        foreach ($this -> pool as $name => $pool) {
            $result = $pool -> destroy();
            $callable && $callable($result);
        }
    }

    public function get(string $name): ?PoolInterface {
        return $this -> pool[$name] ?? null;
    }

    public function has(string $name): bool {
        return array_key_exists($name, $this -> pool);
    }

    public function __get(string $name) {
        // TODO: Implement __get() method.
        return $this->get($name);
    }

}