<?php

namespace iflow\swoole\implement\Tools\Pool\Coroutine;

use Swoole\Coroutine;

class Context {

    /**
     * 获取协程上下文
     * @param ?int $cid
     * @return ?Coroutine\Context
     */
    public static function get(?int $cid = 0): ?Coroutine\Context {
        return Coroutine::getContext($cid ?: 0);
    }

    /**
     * 获取当前协程id
     * @return mixed
     */
    public static function getCid(): int
    {
        return Coroutine::getCid();
    }

    /**
     * 获取当前协程父id
     * @param int|null $cid
     * @return int
     */
    public static function getPCid(?int $cid): int
    {
        return Coroutine::getPcid($cid);
    }

    public static function getDataArrayObject(?int $cid = 0): \ArrayObject
    {
        $context = self::get($cid);
        if (!isset($context['data'])) $context['data'] = new \ArrayObject();
        return $context['data'];
    }

    /**
     * 获取临时数据
     * @param string $key
     * @param mixed|null $default
     * @param ?int $cid
     * @return mixed
     */
    public static function getData(string $key, mixed $default = null, ?int $cid = 0): mixed {
        if (self::hasData($key, $cid)) {
            return self::getDataArrayObject($cid) -> offsetGet($key);
        }
        return $default;
    }

    /**
     * 判断数据是否存在
     * @param string $key
     * @param ?int $cid
     * @return bool
     */
    public static function hasData(string $key, ?int $cid = 0): bool
    {
        return self::getDataArrayObject($cid) -> offsetExists($key);
    }

    /**
     * 设置数据
     * @param string $key
     * @param mixed $value
     * @param ?int $cid
     */
    public static function setData(string $key, mixed $value, ?int $cid = 0): void {
        self::getDataArrayObject($cid) -> offsetSet($key, $value);
    }

    /**
     * 删除数据
     * @param string $key
     * @param ?int $cid
     */
    public static function removeData(string $key, ?int $cid = 0): void {
        if (self::hasData($key, $cid)) {
            self::getDataArrayObject($cid) -> offsetUnset($key);
        }
    }

    /**
     * 如果不存在则写入数据
     * @param string $key
     * @param $value
     * @param int $cid
     * @return mixed
     */
    public static function rememberData(string $key, $value, int $cid = 0): mixed {

        if (self::hasData($key)) return self::getData($key, cid: $cid);

        if ($value instanceof \Closure) {
            // 获取缓存数据
            $value = $value();
        }
        self::setData($key, $value, $cid);
        return $value;
    }

    /**
     * @param ?int $cid
     * @internal
     * 清空数据
     */
    public static function clear(?int $cid = 0): void {
        self::getDataArrayObject($cid)->exchangeArray([]);
    }

}