<?php

namespace iflow\swoole\implement\Tools\Pool;

class Process {

    protected array $processMethods = [];

    protected \Swoole\Process $process;

    public function register(string $key, callable $call): Process {
        $this->processMethods[$key] = $call;
        return $this;
    }


    public function start() {
    }

}