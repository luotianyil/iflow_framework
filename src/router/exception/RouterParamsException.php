<?php


namespace iflow\router\exception;


use Throwable;

class RouterParamsException extends \Exception {
    public function __construct($message = "", $code = 401, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
