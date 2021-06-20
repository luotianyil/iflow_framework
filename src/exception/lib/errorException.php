<?php


namespace iflow\exception\lib;


use Throwable;

class errorException extends \Exception
{
    public function __construct(
        protected int $code,
        protected string $message,
        protected string $file,
        protected int $line
    ){}
}