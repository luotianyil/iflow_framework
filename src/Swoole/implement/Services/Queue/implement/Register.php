<?php

namespace iflow\swoole\implement\Services\Queue\implement;

use iflow\cache\lib\Redis;
use Swoole\Table;

class Register {

    protected Table|Redis $queue;

    public function register() {
    }

}