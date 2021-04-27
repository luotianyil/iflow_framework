<?php


namespace iflow\annotation\lib\value;

#[\Attribute]
class Value
{
    public function __construct(
        private mixed $default = "",
        private string $desc = ""
    ) {}

    /**
     * @return mixed
     */
    public function setDefault(): mixed
    {
        return $this->default;
    }
}