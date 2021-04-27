<?php


namespace iflow\annotation\lib\value\validate;


class Validate
{
    public function __construct(
        private array $rule = [],
        private array $data = [],
        private array $message = []
    ) {}

    public function handle()
    {

    }
}