<?php


namespace iflow\annotation\value;

use iflow\Container\implement\annotation\tools\data\Inject;
use Reflector;

#[\Attribute]
class ConfigValue extends Inject {
    public function __construct(protected string $name, protected mixed $default = '') {
    }

    public function getValue(Reflector $ref, ?object $object = null, array &$args = []): mixed {
        return config($this->name, $this->default);
    }
}