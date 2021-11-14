<?php


namespace iflow\annotation\lib\value;


#[\Attribute]
class ConfigValue extends Inject {
    public function __construct(protected string $name, protected mixed $default = '') {
    }

    public function getValue(\ReflectionParameter|\ReflectionProperty $ref, $object, array &$args = []): mixed {
        return config($this->name, $this->default);
    }
}