<?php


namespace iflow\router\lib\request;

#[\Attribute]
class HeadMapping extends RequestMapping
{
    protected string $method = "HEAD";
}