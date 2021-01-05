<?php
declare(ticks=1);

namespace iflow\Swoole\lib;


use Swoole\Process;

class pid
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

    public function kill($sig, $wait = 0): bool
    {
        $pid = $this->getPid();
        $pid > 0 && Process::kill($pid, $sig);

        if ($wait) {
            $start = time();
            do {
                if (!$this->isRun()) {
                    break;
                }
                usleep(100000);
            } while (time() < $start + $wait);
        }
        return $this->isRun();
    }

    public function isRun(): bool
    {
        $pid = $this->getPid();
        return $pid > 0 && Process::kill($pid, 0);
    }

}