<?php
declare(ticks=1);

namespace iflow\swoole\implement\Tools;

use Swoole\Process;

class Pid {

    public function __construct(protected string $file) {
    }

    public function getPid(): int {
        if (file_exists($this->file) && is_readable($this->file)) {
            return (int) file_get_contents($this->file);
        }
        return 0;
    }

    public function kill($sig): bool {
        while ($this->isRun()) {
            $pid = $this->getPid();
            $pid > 0 && Process::kill($pid, $sig);
            sleep(1);
        }
        return $this->isRun();
    }

    public function isRun(): bool {
        $pid = $this->getPid();
        return $pid > 0 && Process::kill($pid, 0);
    }

}