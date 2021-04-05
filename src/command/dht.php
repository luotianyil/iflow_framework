<?php


namespace iflow\command;


use iflow\console\lib\Command;
use iflow\Swoole\dht\lib\config;

class dht extends Command
{

    public function handle(array $event = [])
    {
        (new \iflow\Swoole\dht\dht(
            new config(config('swoole.dht')),
            ucfirst($event[2])
        )) -> start();
    }

}