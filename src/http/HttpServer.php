<?php


namespace iflow\http;

use iflow\console\lib\Command;
use iflow\Utils\basicTools;

class HttpServer extends Command
{

    private array $config = [];

    public function handle()
    {
        $this->config = config('devServer');
        $this->Console -> outPut ->writeLine(
            (new basicTools()) -> execShell(
            php_run_path() . " -S {$this -> config['host']}:{$this -> config['port']} -t " . $this -> config['document_root']
            )
        );
    }
}