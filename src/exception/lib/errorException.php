<?php


namespace iflow\exception\lib;

class errorException extends \Exception
{
    public function __construct(
        protected $code,
        protected $message,
        protected $file,
        protected $line
    ){}
}