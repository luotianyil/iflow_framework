<?php


namespace iflow\router\exception;


use iflow\exception\lib\HttpException;

class RouterNotFoundException extends HttpException
{
    public function __construct()
    {
        parent::__construct(404, '404 Not-Found');
    }
}