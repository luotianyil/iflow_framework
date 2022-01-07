<?php


namespace iflow\router\lib\request;

#[\Attribute]
class DeleteMapping extends RequestMapping
{
    protected string $method = "DELETE";
}