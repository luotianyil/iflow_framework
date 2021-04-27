<?php


namespace iflow\command;


use iflow\console\lib\Command;

class netPenetrate extends Command
{

    private string $nameSpace = "\\iflow\\Swoole\\netPenetrate\\";

    public function handle(array $event = [])
    {
        // TODO: Implement handle() method.
        $e = ucfirst($event[2] ?? 'server');
        $class = "{$this->nameSpace}{$e}\\{$e}";
        $config = config('swoole.netPenetrate@'. strtolower($e));
        (new $class($config, $this)) -> start();
    }
}