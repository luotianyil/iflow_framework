<?php


namespace iflow\http;

use iflow\console\lib\Command;
use iflow\Utils\basicTools;

class HttpServer extends Command {
    public function handle(array $event = []) {
        config('devServer', call: function ($config) {
            $shell = php_run_path() . " -S {$config['host']}:{$config['port']} -t " . $config['document_root'];
            $this->Console -> outPut ->writeLine((new basicTools()) -> execShell($shell));
            return $config;
        });
    }
}