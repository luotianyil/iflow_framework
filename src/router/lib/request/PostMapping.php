<?php


namespace iflow\router\lib\request;

#[\Attribute]
class PostMapping extends requestMapping
{
    protected string $method = "POST";
}