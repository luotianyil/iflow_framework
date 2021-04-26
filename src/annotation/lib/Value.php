<?php


namespace iflow\annotation\lib;

#[\Attribute]
class Value
{
    public function __construct(
        private mixed $default = "",
        private string $desc = ""
    ) {}
}