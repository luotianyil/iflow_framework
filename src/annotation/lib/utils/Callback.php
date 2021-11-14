<?php


namespace iflow\annotation\lib\utils;


#[\Attribute]
class Callback
{
    public function __construct(protected string $class = '', protected string $method = '') {
    }

    /**
     * 回调方法
     * @param \Reflector $reflector
     * @param $object
     * @param array $args
     * @return mixed
     * @throws \Exception
     */
    public function handle(\Reflector $reflector, $object, array &$args = []): mixed {
        if (function_exists($this->method)) {
            call_user_func($this->method, ...[$reflector, $object, $args]);
        }

        if (!class_exists($this->class)) throw new \Exception('Callback class does not exists', 502);

        if ($this->class === $reflector -> getName()) {
            return call_user_func([new $this -> class, $this->method], ...[$reflector, $object, $args]);
        }

        return app() -> invokeMethod([
            new $this -> class, $this->method
        ], [$reflector, $object, $args]);
    }
}