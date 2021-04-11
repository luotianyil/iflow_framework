<?php


namespace iflow\Utils\Tools;


class Timer
{

    public static function tick($ms, \Closure $closure)
    {
        if (class_exists(\Swoole\Timer::class)) {
            return \Swoole\Timer::tick($ms, $closure);
        }
        do {
            sleep(floatval(bcdiv("{$ms}", "1000")));
            call_user_func($closure);
        } while(true);
    }
}