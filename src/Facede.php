<?php


namespace iflow;

class Facede
{

    protected static bool $isNew = false;

    protected static function  createFacade(string $class = '', array $args = [], bool $isNew = false) {
        $class = $class?:static::class;
        $class =  static::getFacedeClass()?:$class;
        $isNew = static::$isNew?:$isNew;
        return Container::getInstance() -> make($class, $args, $isNew);
    }

    // 获取类
    protected static function getFacedeClass() : string
    { return ''; }

    public static function instance(...$args) {
        if (__CLASS__ != static::class) {
            return self::createFacade('', $args);
        }
        return null;
    }

    protected static function make(string $class = '', array $args = [], bool $newInstance = false) {
        if (__CLASS__ != static::class) {
            return self::__callStatic('make', $args);
        }
        return self::createFacade($class, $args, $newInstance);
    }

    public static function __callStatic($method, $params) {
        return call_user_func_array([static::createFacade(), $method], $params);
    }

}