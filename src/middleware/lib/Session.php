<?php


namespace iflow\middleware\lib;


use iflow\App;

class Session
{
    public function handle(App $app, $next)
    {
        $app -> make(\iflow\session\Session::class) -> initializer();
        return $next($app);
    }
}