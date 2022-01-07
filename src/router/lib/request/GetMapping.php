<?php


namespace iflow\router\lib\request;

#[\Attribute]
class GetMapping extends RequestMapping
{
    protected string $method = "GET";
}