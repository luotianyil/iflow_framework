<?php


namespace iflow\router\lib\request;

#[\Attribute]
class DeleteMapping extends requestMapping
{
    protected string $method = "DELETE";
}