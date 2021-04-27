<?php


namespace iflow\annotation\lib\value;

#[\Attribute]
class Value
{
    public function __construct(
        private mixed $default = "",
        private string $desc = ""
    ) {}

    public function handle(\ReflectionProperty $ref, $object)
    {
        try {
            return $ref -> getValue($object);
        } catch (\Error) {
            $ref -> setValue(
                $object, $ref -> getDefaultValue() ?: $this->default
            );
        }
        return true;
    }

    /**
     * @return mixed
     */
    public function getDefault(): mixed
    {
        return $this->default;
    }
}