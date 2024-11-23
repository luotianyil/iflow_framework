<?php

namespace iflow\swoole\implement\Tools\Pool;

use Swoole\Process as SProcess;

class Process {

    /**
     * @var array<string, SProcess>
     */
    protected array $processMethods = [];

    public function register(string $processName, callable $call, ...$args): Process {
        $this->processMethods[$processName] = new SProcess($call, ...$args);
        return $this;
    }


    public function start(bool $daemon = false): Process {
        foreach ($this->processMethods as $pName => $process) {
            $process -> name($pName);
            $process -> start();
        }

        if ($daemon) SProcess::daemon();
        return $this;
    }


    public function getProcess(string $processName): SProcess {
        if (empty($this->processMethods[$processName])) throw new \Exception('Process Non');
        return $this->processMethods[$processName];
    }

}