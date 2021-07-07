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
            if (method_exists($this, $key) && call_user_func([$this, $key])) break;
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
        $this->response = $this -> services -> app -> make(Response::class, [], true) -> initializer($response);
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

        // 执行控制器类注解
        app() -> runAttributes($this->refController, $this->refController, $controller);

        // 执行方法
        return $this->send(
            app() -> invokeMethod(
                [$controller, $this->requestController[1]],
                $this->routerBindParams
            )
        );
    }

    /**
     * 设置Bean 参数
     * @param array $params
     * @return object
     * @throws \ReflectionException|valueException
     */
    protected function setInstanceValue(array $params): object
    {
        $keys = array_keys($params);
        if (empty($params[$keys[0]]['class']) || isset($params['class'])) {
            return new $params['class'];
        }
        $class = $params[$keys[0]]['class'];

        // 当类存在时
        if (class_exists($class)) {
            $ref = new \ReflectionClass($class);
            $object = $ref -> newInstance();

            foreach ($params as $paramName => $paramValue) {
                if (!$paramValue['default']) continue;
                if ($paramValue['type'][0] === 'class') {
                    $paramValue['default'] = $this -> setInstanceValue($paramValue['default']);
                }
                $ref -> getProperty($paramName) -> setValue(
                    $object, $paramValue['default']
                );
            }
            // 执行Bean注解
            app() -> runAttributes($ref, ...[$ref, $object, $this]);
        } else throw new valueException(message() -> parameter_error("dataObject: ${class} IsNull"));
        return $object;
    }
}
