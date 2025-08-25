<?php


namespace iflow\cache;

use iflow\cache\Adapter\File\File;
use iflow\cache\Adapter\Redis\Redis;
use iflow\Container\Container;
use iflow\Container\implement\annotation\exceptions\AttributeTypeException;
use iflow\Container\implement\generate\exceptions\InvokeClassException;
use iflow\Container\implement\generate\exceptions\InvokeFunctionException;

/**
 * @mixin Redis
 * @mixin File
 * Class Cache
 * @package iflow\cache
 */
class Cache {

    protected array $config = [];

    protected string $namespace = '\\iflow\\cache\\Adapter\\';

    protected function getConfig(string|array $default = ''): void {
        $this->config = is_string($default) ? config('cache@stores.'.$default) : $default;
    }

    /**
     * @param string|array $name
     * @return Redis|File
     * @throws AttributeTypeException | InvokeClassException | InvokeFunctionException
     */
    public function store(string|array $name = ''): Redis | File {
        if (is_string($name)) {
            $name = $name ?: config('cache@default');
        }
        $this->getConfig($name);
        if (!$this->config) throw new \Exception('cache config null');

        $type = ucfirst($this->config['type']);
        $class = sprintf("%s%s\\%s", $this->namespace, $type, $type);

        if (app() -> has($class))
            return app() -> get($class);

        return app() -> make($class) -> initializer($this->config);
    }
}