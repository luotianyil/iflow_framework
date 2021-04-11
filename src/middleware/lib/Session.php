<?php


namespace iflow\middleware\lib;


use iflow\App;

class Session
{
    public function handle(App $app, $next)
    {
        \iflow\facade\Session::initializer();
        return $next($app);
    }
}