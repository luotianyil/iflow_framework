<?php


namespace iflow\command;


use iflow\console\Adapter\Command;
use iflow\socket\workman\http\httpServer;

class workMan extends Command
{

    public function handle(array $event = []): bool {
        // TODO: Implement handle() method.
        $config = config('socket@workman');
        if (empty($config)) {
            $this->Console -> writeConsole -> writeLine('WorkerMan Config is empty !!!');
            return true;
        }
        $configName = $this->getArgument('config', $this->getArgument('--c'));
        if (!$configName) {
            $configName = $config['default'];
        }

        $this->Console -> writeConsole -> writeLine((new httpServer($config[$configName])) -> start());
        return true;
    }
}