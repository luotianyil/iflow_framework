<?php


namespace iflow\router\lib\request;

#[\Attribute]
class PutMapping extends requestMapping
{
    protected string $method = "PUT";
}