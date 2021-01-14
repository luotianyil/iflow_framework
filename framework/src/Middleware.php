<?php


namespace iflow;

use iflow\pipeline\pipeline;

class Middleware
{

    protected Response $response;
    protected Request $request;
    protected App $app;
    protected pipeline $pipeline;

    public function initializer(App $app, Request $request, Response $response)
    {
        $this->app = $app;
        $this->response = $response;
        $this->pipeline = new pipeline();
        $this->request = $request;
        return $this->addMiddleware();
    }

    // add Middleware
    public function addMiddleware()
    {
        foreach (config('middleware') as $key) {
            $this->pipeline -> through($key);
        }
        return $this->thenMiddleware();
    }

    public function thenMiddleware()
    {
        $then = $this->pipeline -> process($this->app, $this -> request, $this->response);
        if ($then instanceof Response) return $then;
        return $then;
    }

}