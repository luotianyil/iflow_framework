<?php

namespace iflow\swoole\implement\Services\Queue\implement\Topics;

use iflow\cache\Adapter\Redis\Redis;
use iflow\swoole\implement\Tools\Tables;

class Topic {

    protected Tables|Redis $Queue;

}