<?php
namespace app;

use iflow\router\lib\Router;

#[Router('/')]
class controller
{
    #[Router('index')]
    public function index(bean $bean, string $a, int $c = 1)
    {
    }

    #[Router('index/<?:a>/<[0-9]{1}:b>')]
    public function indexPath(bean $bean, string $a, int $c = 1, array $ccc = [])
    {
        return [];
    }
}