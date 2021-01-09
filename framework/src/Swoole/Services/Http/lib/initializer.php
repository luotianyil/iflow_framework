<?php


namespace iflow\Swoole\Services\Http\lib;


use iflow\Middleware;
use iflow\Request;
use iflow\Response;
use iflow\router\RouterBase;
use iflow\Swoole\Services\Services;

class initializer
{

    public Services $services;
    public Request $request;
    public Response $response;

    public array $router;

    public function __initializer(Services $services, $request, $response)
    {
        $this->services = $services;
        $this->setRequest($request)
            -> setResponse($response)
            -> validateRouter();
    }

    // 初始化请求数据
    public function setRequest($request): static
    {
        $this->request = $this -> services -> app -> make(Request::class) -> initializer($request);
        return $this;
    }

    // 初始化响应数据
    public function setResponse($response): static
    {
        $this->response = $this -> services -> app -> make(Response::class) -> initializer($response);
        return $this;
    }

    // 验证路由
    protected function validateRouter()
    {
        $this->router = app() -> make(RouterBase::class) -> validateRouter($this->request -> request_uri, $this->request -> request_method);

        if (!$this->router) {
            $this->response -> notFount();
        } else {
            $middleware = $this->services -> app
                -> make(Middleware::class)
                -> initializer($this->services -> app, $this->request, $this->response);
            // 中间件返回 响应实例结束
            if ($middleware instanceof Response) {
                $middleware -> send();
            } else {
                $this->newInstanceController();
            }
        }
    }

    protected function newInstanceController()
    {
        [$controller, $action] = explode('@', $this->router['action']);
        if (!class_exists($controller)) $this->response -> notFount();

        $ref = new \ReflectionClass($controller);
        $controller = $ref -> newInstance(...[$this->request, $this->response]);
        if (!method_exists($controller, $action)) $this->response -> notFount();

        $response = call_user_func([$controller, $action], ...$this->bindParam($this->router['parameter']));

        if ($response instanceof Response) $response -> send();
        if (is_array($response)) json($response) -> send();
        $this->response -> data($response) -> send();
    }

    protected function bindParam(array $params = []): array
    {
        $parameter = [];
        foreach ($params as $key => $value) {
            if (empty($value['default'])) {
                $parameter[] = $this->setInstanceValue($value);
            } else {
                $parameter[] = $value['default'];
            }
        }
        return $parameter;
    }


    protected function setInstanceValue(array $params): mixed
    {
        $object = [];
        if (count($params) > 0 && isset($params[0]['class'])) {
            $class = $params[0]['class'];
            if (class_exists($class)) {
                $object = (new \ReflectionClass($class)) -> newInstance();
                foreach ($params as $key => $value) {
                    $param = $value['name'];
                    $object -> $param = $value['default'];
                }
            } else {
                return $object['default'];
            }
        }
        return $object;
    }
}
