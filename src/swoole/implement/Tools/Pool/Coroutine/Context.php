<?php

namespace iflow\swoole\implement\Tools\Pool\Coroutine;

use Swoole\Coroutine;

class Context {

    /**
     * 获取协程上下文
     * @param int|null $cid
     * @return mixed
     */
    public static function get(int $cid = null): mixed
    {
        return Coroutine::getContext($cid);
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
     * @param int $cid
     * @return int
     */
    public static function getPCid(int $cid): int
    {
        return Coroutine::getPcid($cid);
    }

    public static function getDataArrayObject($cid = null): \ArrayObject
    {
        $context = self::get($cid);
        if (!isset($context['data'])) $context['data'] = new \ArrayObject();
        return $context['data'];
    }

    /**
     * 获取临时数据
     * @param string $key
     * @param mixed|null $default
     * @param null $cid
     * @return mixed
     */
    public static function getData(string $key, mixed $default = null, $cid = null): mixed {
        if (self::hasData($key, $cid)) {
            return self::getDataArrayObject($cid);
        }
        return $default;
    }

    /**
     * 判断数据是否存在
     * @param string $key
     * @param null $cid
     * @return bool
     */
    public static function hasData(string $key, $cid = null): bool
    {
        return self::getDataArrayObject($cid) -> offsetExists($key);
    }

    /**
     * 设置数据
     * @param string $key
     * @param mixed $value
     * @param null $cid
     */
    public static function setData(string $key, mixed $value, $cid = null): voids {
        self::getDataArrayObject($cid) -> offsetSet($key, $value);
    }

    /**
     * 删除数据
     * @param string $key
     * @param null $cid
     */
    public static function removeData(string $key, $cid = null): void {
        if (self::hasData($key, $cid)) {
            self::getDataArrayObject($cid) -> offsetUnset($key);
        }
    }

    /**
     * 如果不存在则写入数据
     * @param string $key
     * @param $value
     * @param $cid
     * @return mixed
     */
    public static function rememberData(string $key, $value, $cid): mixed
    {
        if (self::hasData($key)) {
            return self::getData($key, cid: $cid);
        }
        if ($value instanceof \Closure) {
            // 获取缓存数据
            $value = $value();
        }
        self::setData($key, $value, $cid);
        return $value;
    }

    /**
     * @param null $cid
     * @internal
     * 清空数据
     */
    public static function clear($cid = null)
    {
        self::getDataArrayObject($cid)->exchangeArray([]);
    }

}