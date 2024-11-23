<?php


namespace iflow\swoole\implement\Services\Kafka;

use iflow\console\Adapter\Command;
use iflow\swoole\implement\Services\Kafka\implement\Consumer;

class Services extends Command {

    public function handle(array $event = []) {
        // TODO: Implement handle() method.
        $this->app -> make(Consumer::class, [ $this ]) -> handle();
    }

}