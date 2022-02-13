<?php


namespace iflow\command;


use iflow\console\lib\Command;
use iflow\socket\workman\http\httpServer;

class workMan extends Command
{

    public function handle(array $event = [])
    {
        // TODO: Implement handle() method.
        $config = config('socket@workman');
        if (empty($config)) {
            $this->Console -> outPut -> writeLine('WorkMan Config is empty !!!');
            return true;
        }
        $configName = $this->getArgument('config', $this->getArgument('--c'));
        if (!$configName) {
            $configName = $config['default'];
        }

        $this->Console -> outPut -> writeLine((new httpServer($config[$configName])) -> start());
        return true;
    }
}