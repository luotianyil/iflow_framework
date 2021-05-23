<?php


namespace iflow\router\lib\request;

#[\Attribute]
class HeadMapping extends requestMapping
{
    protected string $method = "HEAD";
}