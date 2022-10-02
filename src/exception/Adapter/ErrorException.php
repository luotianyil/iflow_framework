<?php


namespace iflow\exception\Adapter;

class ErrorException extends \Exception
{
    public function __construct(
        protected $code,
        protected $message,
        protected string $file,
        protected int $line
    ){}
}