<?php


namespace iflow\Swoole\Services\Http\lib;


use iflow\annotation\lib\value\Exception\valueException;
use iflow\http\lib\Cookie;
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
            try {
                if (method_exists($this, $key) && call_user_func([$this, $key])) break;
            } catch (valueException $valueException) {
                // 此处捕获参数异常
                $this->validateResponse($valueException -> getError());
                break;
            }
        }
    }

    // 初始化请求数据
    public function setRequest($request): static
    {
        $this->services -> app -> make(Cookie::class, [
            $request -> cookie ?: []
        ]);
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
    protected function validateRouter(): bool
    {
        $this->router = app() -> make(RouterBase::class) -> checkRule(
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

    /**
     * 实例化控制器
     * @return bool
     * @throws \ReflectionException
     */
    protected function newInstanceController(): bool
    {
        $this->requestController = explode('@', $this->router['action']);
        if ($this->validateResponse(class_exists($this->requestController[0]))) return true;
        $this->refController = new \ReflectionClass($this->requestController[0]);
        if ($this->validateResponse($this->refController -> hasMethod($this->requestController[1]))) return true;
        return false;
    }

    /**
     * 执行控制器方法
     * @return bool
     * @throws \ReflectionException
     */
    protected function startController(): bool
    {
        $controller =
            $this->refController -> getConstructor() ?
                $this->refController -> newInstance(...[$this->request, $this->response])
                : $this->refController -> newInstance();

        return $this->send(call_user_func([$controller, $this->requestController[1]], ...$this->bindParam(
            $this->router['parameter']
        )));
    }

    /**
     * 设置Bean 参数
     * @param array $params
     * @return mixed
     * @throws \ReflectionException
     */
    protected function setInstanceValue(array $params): mixed
    {
        $keys = array_keys($params);
        $class = $params[$keys[0]]['class'];
        $object = [];

        if (count($params) > 0 && class_exists($class)) {
            $ref = new \ReflectionClass($class);
            $object = $ref -> newInstance();
            foreach ($params as $key => $value) {
                if (isset($value['default'])) {
                    if ($value['type'][0] === 'class') {
                        $value['default'] = $this->setInstanceValue($value['default']);
                    }
                    $ref -> getProperty($value['name']) -> setValue(
                        $object, $value['default'] ?? ''
                    );
                }
            }
            // 获取 Bean注解 并执行
            $attributes = $ref -> getAttributes();
            foreach ($attributes as $attr) {
                call_user_func([$attr -> newInstance(), 'handle'], ...[$ref, $object, $this]);
            }
        } else return $object['default'];
        return $object;
    }
}
