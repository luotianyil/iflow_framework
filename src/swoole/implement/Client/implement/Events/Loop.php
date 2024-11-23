<?php

namespace iflow\swoole\implement\Client\implement\Events;

use Swoole\Event;

class Loop {

    public function attach(
        mixed $sock,
        callable $read_callback = null,
        callable $write_callback = null,
        int $flags = SWOOLE_EVENT_READ
    ) {
        return Event::add(...func_get_args());
    }

    public function eventSet() {
        return Event::set(...func_get_args());
    }

    /**
     * 移除事件
     * @param mixed $sock
     * @return mixed
     */
    public function detach(mixed $sock): mixed {
        if (!Event::isset($sock)) return false;
        return Event::del($sock);
    }


    public function wait() {
        return Event::wait();
    }

}