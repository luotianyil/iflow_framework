<?php


namespace iflow\router\lib\request;

#[\Attribute]
class PatchMapping extends RequestMapping
{
    protected string $method = "PATCH";
}
