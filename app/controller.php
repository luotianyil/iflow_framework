<?php
namespace app;

use iflow\router\lib\Router;

#[Router('/')]
class controller
{
    #[Router('index')]
    public function index(bean $bean)
    {
    }

}