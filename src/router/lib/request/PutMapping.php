<?php


namespace iflow\router\lib\request;

#[\Attribute]
class PutMapping extends RequestMapping
{
    protected string $method = "PUT";
}