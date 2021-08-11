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
        $attribute = $reflectionProperty -> getAttributes($name)[0] ?? null;
        if ($attribute) {
            call_user_func([$attribute -> newInstance(), 'handle'], ...[$reflectionProperty, $this->object]);
        }
    }
}