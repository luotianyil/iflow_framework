<?php


namespace iflow\http;
use iflow\console\Adapter\Command;
use iflow\Utils\BasicTools;

class HttpServer extends Command {

    public function handle(array $event = []) {
        config('devServer', call: function ($config) {

            $phpRuntimePath = $this -> getArgument('--phprun', php_run_path());

            $shell = $phpRuntimePath . " -S {$config['host']}:{$config['port']} -t " . $config['document_root'];
            $this->Console -> writeConsole ->writeLine((new BasicTools()) -> execShell($shell));
            return $config;
        });
    }

}