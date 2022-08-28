<?php


namespace iflow;

use iflow\exception\lib\HttpResponseException;
use iflow\Pipeline\Pipeline;
use Psr\Http\Message\ResponseInterface;

class Middleware {

    protected App $app;
    protected Pipeline $pipeline;

    protected array $middleware = [];

    public function initializer(App $app): bool {
        $this->app = $app;
        $this->pipeline = new Pipeline();
        return $this->throughMiddleware() -> thenMiddleware();
    }

    // 中间件预处理
    public function throughMiddleware(): static {
        $this->middleware = config('middleware');
        $this->pipeline -> through(
            array_map(function ($middleware) {
                return function ($request, $next) use ($middleware) {
                    [$class, $params] = is_array($middleware) ? $middleware : [$middleware, []];
                    $response = call_user_func([$this->app->make($class), 'handle'], $request, $next, ...$params);
                    if ($response instanceof Response || $response instanceof ResponseInterface) {
                        // 抛出响应异常
                        throw new HttpResponseException($response);
                    }
                };
            }, $this->middleware)
        );
        return $this;
    }

    // 执行中间件
    public function thenMiddleware(): bool {
        $this->pipeline -> process($this->app);
        return true;
    }
}