<?php


namespace iflow\annotation\lib\value;

use iflow\annotation\lib\value\validate\ValidateRule;

#[\Attribute]
class Data
{

    protected object $object;

    protected array $methodAttribute = [
        Value::class,
        NotNull::class,
        FilterArg::class,
        ValidateRule::class
    ];

    public function handle(\ReflectionClass $ref, $object)
    {
        $this->object = $object;
        $properties = $ref -> getProperties();
        foreach ($properties as $proper) {
            foreach ($this->methodAttribute as $attrName) {
                $this->properAttributes($proper, $attrName);
            }
        }
    }

    protected function properAttributes(\ReflectionProperty $reflectionProperty, string $name)
    {
        array_map(
            fn($attribute) => call_user_func([$attribute -> newInstance(), 'handle'], ...[$reflectionProperty, $this->object]),
            $reflectionProperty -> getAttributes($name)
        );
    }
}