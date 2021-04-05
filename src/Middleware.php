<?php


namespace iflow;

use iflow\pipeline\pipeline;

class Middleware
{
    protected App $app;
    protected pipeline $pipeline;
    protected Response $response;

    protected array $middleware = [];

    public function initializer(App $app): bool
    {
        $this->app = $app;
        $this->pipeline = new pipeline();
        return $this->throughMiddleware()
            -> thenMiddleware();
    }

    // 中间件预处理
    public function throughMiddleware(): static
    {
        $this->middleware = config('middleware');
        $this->pipeline -> through(
            array_map(function ($middleware) {
                return function ($request, $next) use ($middleware) {
                    [$class, $params] = is_array($middleware) ? $middleware : [$middleware, []];
                    $response = call_user_func([$this->app->make($class), 'handle'], $request, $next, ...$params);
                    if ($response instanceof Response) {
                        $this->response = $response;
                        throw new \Exception('middleware returns response');
                    }
                };
            }, $this->middleware)
        );
        return $this;
    }

    // 执行中间件
    public function thenMiddleware(): Response|bool
    {
        try {
            $this->pipeline -> process($this->app);
            return true;
        } catch (\Exception) {
            return $this->response;
        }
    }

}