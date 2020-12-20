<?php


namespace iflow\app\controller;

use iflow\router\lib\Router;

#[Router('index')]
class IndexController
{
    #[Router('index', 'get')]
    public function Index(): int
    {
        return 123;
    }
}