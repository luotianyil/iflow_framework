<?php


namespace iflow\annotation\lib\value;

use iflow\annotation\lib\value\validate\ValidateRule;

#[\Attribute]
class Data
{
    private array $methodAttribute = [
        Value::class,
        NotNull::class,
        ValidateRule::class
    ];

    public function handle(\ReflectionClass $ref)
    {
        $methods = $ref -> getMethods();
        foreach ($methods as $method) {
            $attrMethod = $method -> getAttributes($this->methodAttribute);

        }
    }
}