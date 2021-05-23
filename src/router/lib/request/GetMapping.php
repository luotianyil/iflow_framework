<?php


namespace iflow\router\lib\request;

#[\Attribute]
class GetMapping extends requestMapping
{
    protected string $method = "GET";
}