<?php

namespace iflow\swoole\implement\Services\Queue\implement\Topics;


class Push {


    /**
     * 发布消费
     * @param int $msgflags
     * @param mixed $payload
     * @param string $key
     * @param int $expired
     * @return bool
     */
    public function poll(
        int $msgflags = 0,
        mixed $payload = '',
        string $key = '',
        int $expired = 0
    ): bool {

    }

}