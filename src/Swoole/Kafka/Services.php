<?php


namespace iflow\Swoole\Kafka;

use iflow\console\lib\Command;
use iflow\Swoole\Kafka\lib\consumer;

class Services extends Command
{
    public function handle(array $event = [])
    {
        // TODO: Implement handle() method.
        $this->app -> make(consumer::class, [
            $this
        ]) -> handle();
    }
}