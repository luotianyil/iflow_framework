<?php


namespace iflow\Swoole\Services\Http\lib;


use iflow\Middleware;
use iflow\Request;
use iflow\Response;
use iflow\router\RouterBase;
use iflow\Swoole\Services\Services;
use iflow\Swoole\Services\WebSocket\socketio\SocketIo;

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
        $this->request = $this -> services -> app -> make(Request::class, [], true) -> initializer($request);
        return $this;
    }

    // 初始化响应数据
    public function setResponse($response): static
    {
        $this->response = $this -> services -> app -> make(Response::class, [],true) -> initializer($response);
        return $this;
    }

    // 验证路由
    protected function validateRouter()
    {

        $middleware = $this->services -> app
            -> make(Middleware::class)
            -> initializer($this->services -> app, $this->request, $this->response);
        // 中间件返回 响应实例结束
        if ($middleware instanceof Response) {
            $middleware -> send();
        } else {

            if ($this->isSocketIo($this->request -> request_uri)) {
                return;
            }
            if ($this->isStaticResources($this->request -> request_uri)) {
                return;
            }
            if ($this->isRequestApi($this->request -> request_uri)) {
                return;
            }

            $this->router = app() -> make(RouterBase::class) -> validateRouter(
                $this->request -> request_uri,
                $this->request -> request_method,
                $this->request -> params() ?? []
            );

            return !$this->router ? $this->response -> notFount(): $this->newInstanceController();
        }

        return true;
    }

    protected function newInstanceController()
    {
        [$controller, $action] = explode('@', $this->router['action']);
        if (!class_exists($controller)) $this->response -> notFount();

        $ref = new \ReflectionClass($controller);
        $controller =
        $ref -> getConstructor() ?
            $ref -> newInstance(...[$this->request, $this->response]) : $ref -> newInstance();
        if (!method_exists($controller, $action)) return $this->response -> notFount();

        return $this->send(call_user_func([$controller, $action], ...$this->bindParam(
            $this->router['parameter']
        )));
    }

    protected function bindParam(array $params = []): array
    {
        $parameter = [];
        foreach ($params as $key => $value) {
            if (isset($value['default'])) {
                $parameter[] = $value['default'];
            } else {
                $parameter[] = $this->setInstanceValue($value);
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
                $ref = new \ReflectionClass($class);
                $object = $ref -> newInstance();
                foreach ($params as $key => $value) {
                    $ref -> getProperty($value['name']) -> setValue($object, $value['default']);
                }
            } else {
                return $object['default'];
            }
        }
        return $object;
    }

    protected function isRequestApi(string $url = ''): Response|bool
    {
        $url = trim($url, '/');
        if ($url === config('app@api_path')) {
            message() -> success('success', config('router')) -> send();
            return true;
        }
        return false;
    }

    protected function isStaticResources(string $url = '')
    {
        $url = explode('/', trim($url, '/'));
        $rule = config('app@resources.file');
        if ($url[0] === $rule['rule']) {
            array_splice($url, 0, 1);
            $url = str_replace('/', DIRECTORY_SEPARATOR, implode('/', $url));
            response() -> sendFile($rule['rootPath'] . DIRECTORY_SEPARATOR . $url);
            return true;
        }
        return false;
    }

    protected function isSocketIo($url = '')
    {
        $url = explode('/', trim($url, '/'));
        if ($this->services -> configs['websocket']['enable']) {
            if ($url[0] === 'socket.io') {
                $SocketIo = new SocketIo();
                $SocketIo -> config = $this->services -> configs['websocket'];
                return $this->send($SocketIo-> __initializer($this->request, $this->response));
            }
        }
        return false;
    }

    protected function send($response)
    {
        if ($response instanceof Response) {
            $response -> send();
        } else if (!is_string($response)) {
            json($response) -> send();
        } else if ($this->response) {
            $this->response -> data($response) -> send();
        }

        return true;
    }
}
