<?php


namespace iflow\middleware;


use iflow\App;

class Session
{
    public function handle(App $app, $request, $response, array $header = []): bool
    {
        $app -> make(\iflow\session\Session::class) -> initializer();
        return true;
    }
}