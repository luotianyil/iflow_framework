<?php


namespace iflow\Utils\Tools;


class Timer
{

    protected static mixed $timer;

    /**
     * 设置定时任务
     * @param $ms
     * @param \Closure $closure
     * @return mixed
     */
    public static function tick($ms, \Closure $closure): mixed
    {
        if (class_exists(\Swoole\Timer::class)) {
            static::$timer = \Swoole\Timer::tick($ms, $closure);
        } else {
            static::$timer = \Workerman\Lib\Timer::add($ms / 1000, $closure);
        }
        return static::$timer;
    }


    public static function after($ms, \Closure $closure)
    {
        if (class_exists(\Swoole\Timer::class)) {
            static::$timer = \Swoole\Timer::after($ms, $closure);
        } else {
            static::$timer = \Workerman\Lib\Timer::add($ms / 1000, $closure);
        }
        return static::$timer;
    }


    /**
     * 重置定时任务
     * @param $timer
     * @return mixed
     */
    public static function clear($timer): mixed
    {
        if (class_exists(\Swoole\Timer::class)) {
            static::$timer = \Swoole\Timer::clear($timer);
        } else {
            static::$timer = \Workerman\Lib\Timer::del($timer);
        }
        return static::$timer;
    }
}