<?php

namespace iflow\swoole\implement\Tools\Pool;

use Swoole\Process as SProcess;

class Process {

    /**
     * @var array<string, SProcess>
     */
    protected array $processMethods = [];

    public function register(string $key, callable $call, ...$args): Process {
        $this->processMethods[$key] = new SProcess(
            $call,
            ...$args
        );
        return $this;
    }


    public function start(): Process {
        foreach ($this->processMethods as $pName => $process) {
            $process -> start();
        }

        return $this;
    }


    public function getProcess(string $key): SProcess {
        if (empty($this->processMethods[$key])) throw new \Exception('Process Non');
        return $this->processMethods[$key];
    }

}