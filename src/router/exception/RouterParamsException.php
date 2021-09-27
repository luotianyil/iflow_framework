<?php


namespace iflow\router\exception;


use iflow\exception\lib\HttpException;

class RouterParamsException extends HttpException {
    public function __construct($message = "", $code = 401, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
