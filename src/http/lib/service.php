<?php


namespace iflow\http\lib;

class service
{
    public float $runMemoryUsage = 0.00;

    public function __construct(
        public $app
    ){}
}