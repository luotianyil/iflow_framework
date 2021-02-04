<?php


namespace iflow\Swoole\Services\Http\lib;


use iflow\Request;
use iflow\Response;
use iflow\router\RouterBase;


class initializer extends requestTools
{

    public function __initializer($request, $response)
    {
        $this->setRequest($request)
            -> setResponse($response);

        foreach ($this->runProcess as $key) {
            if (method_exists($this, $key) && call_user_func([$this, $key])) {
                break;
            }
        }
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

        if ($this->isSocketIo($this->request -> request_uri)) return true;
        if ($this->isStaticResources($this->request -> request_uri)) return true;
        if ($this->isRequestApi($this->request -> request_uri)) return true;

        $this->router = app() -> make(RouterBase::class) -> validateRouter(
            $this->request -> request_uri,
            $this->request -> request_method,
            $this->request -> params() ?? []
        );

        if (!$this->router) {
            $this->response -> notFount();
            return true;
        }
        return $this->newInstanceController();
    }

    protected function newInstanceController(): bool
    {
        $this->requestController = explode('@', $this->router['action']);
        if ($this->validateResponse(class_exists($this->requestController[0]))) return true;
        $this->refController = new \ReflectionClass($this->requestController[0]);
        if ($this->validateResponse($this->refController -> hasMethod($this->requestController[1]))) return true;
        return false;
    }

    protected function startController() {
        $controller =
            $this->refController -> getConstructor() ?
                $this->refController -> newInstance(...[$this->request, $this->response])
                : $this->refController -> newInstance();

        return $this->send(call_user_func([$controller, $this->requestController[1]], ...$this->bindParam(
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
}
