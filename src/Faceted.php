<?php


namespace iflow;

use iflow\Container\Container;

abstract class Faceted {

    protected static bool $isNew = false;

    protected static function  createFacade(string $class = '', array $args = [], bool $isNew = false): object {
        $class = $class?:static::class;
        $class =  static::getFaceClass()?:$class;
        $isNew = static::$isNew ?: $isNew;
        return Container::getInstance() -> make($class, $args, $isNew);
    }

    // 获取类
    abstract protected static function getFaceClass() : string;

    public static function instance(...$args): ?object {
        if (__CLASS__ !== static::class) {
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
