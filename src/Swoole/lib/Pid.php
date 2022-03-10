<?php
declare(ticks=1);

namespace iflow\Swoole\lib;


use Swoole\Process;

class Pid
{

    protected string $file = '';

    public function __construct($file)
    {
        $this->file = $file;
    }

    public function getPid(): int
    {
        if (is_readable($this->file)) {
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