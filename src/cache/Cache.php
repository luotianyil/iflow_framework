<?php


namespace iflow\cache;

use iflow\cache\lib\File;
use iflow\cache\lib\Redis;

/**
 * @mixin Redis
 * @mixin File
 * Class Cache
 * @package iflow\cache
 */
class Cache
{

    protected array $config = [];

    protected string $namespace = '\\iflow\\cache\\lib\\';

    protected function getConfig(string|array $default = '')
    {
        $this->config = is_string($default) ? config('cache@stores.'.$default) : $default;
    }

    public function store(string|array $name = ''): Redis | File
    {
        if (is_string($name)) {
            $name = $name ?: config('cache@default');
        }
        $this->getConfig($name);
        if (!$this->config) throw new \Exception('cache config null');
        $class = $this->namespace . ucfirst($this->config['type']);
        return (new $class) -> initializer($this->config);
    }



}