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
                $object, $ref -> getDefaultValue() ?: $this->defaultIsClass()
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

    /**
     * 验证是否为类
     * @return mixed|string
     */
    private function defaultIsClass(): mixed
    {
        if (is_string($this->default) && class_exists($this->default)) {
            $this->default = app() -> make($this->default);
        }
        return $this->default;
    }
}