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

    protected function getConfig(string $default = '')
    {
        $this->config = config('cache@stores.'.$default);
    }

    public function store(string $name = ''): Redis | File
    {

        $name = $name ?: config('cache@default');
        $this->getConfig($name);

        if (!$this->config) throw new \Exception('cache config null');
        $class = $this->namespace . ucfirst($this->config['type']);
        return (new $class) -> initializer($this->config);
    }



}