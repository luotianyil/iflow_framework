<?php


namespace iflow\router\lib\request;

#[\Attribute]
class PostMapping extends RequestMapping
{
    protected string $method = "POST";
}