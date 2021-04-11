<?php


namespace iflow\http;

use iflow\console\lib\Command;
use iflow\Utils\basicTools;

class HttpServer extends Command
{

    public function handle(array $event = [])
    {
        $config = config('devServer');
        $this->Console -> outPut ->writeLine(
            (new basicTools()) -> execShell(
            php_run_path() . " -S {$config['host']}:{$config['port']} -t " . $config['document_root']
            )
        );
    }
}