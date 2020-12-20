<?php


namespace iflow\app\live_web\controller;

use iflow\router\lib\Router;

#[Router('home')]
class Index
{
    #[Router('index')]
    public function IndexMethod()
    {

   }

    #[Router('home', methods: 'post')]
    public function IndexMethods()
    {

    }

}