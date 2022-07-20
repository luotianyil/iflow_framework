<?php

namespace iflow\http\Kernel\Request;

use iflow\Container\Container;
use iflow\Container\implement\annotation\tools\data\exceptions\ValueException;
use iflow\Container\implement\annotation\traits\Execute;
use iflow\http\Kernel\Exception\RequestValidateException;
use iflow\http\lib\Cookie;
use iflow\Request;
use iflow\Response;
use ReflectionClass;

class RequestInitializer extends RequestVerification {

    /**
     * 请求回调事件
     * @param object|null $request
     * @param object|null $response
     * @param float $startTime
     * @return RequestVerification
     */
    public function trigger(object $request = null, object $response = null, float $startTime = 0.00): RequestVerification {
        $this->setRequest($request) -> setResponse($response);

        foreach ($this->RunProcessMethods as $key) {
            if (method_exists($this, $key) && call_user_func([$this, $key])) break;
        }

        event('RequestEndEvent', $startTime);
        return $this;
    }


    /**
     * 返回响应内容
     * @return bool
     */
    protected function ReturnsResponseBody(): bool {
        $controller = app($this->ReflectionClass -> getName(), [ $this->request, $this->response ], true);
        // 执行方法
        return $this->send(app() -> invoke([$controller, $this->RequestController[1]], $this->RequestQueryParams));
    }

    /**
     * 初始化请求数据
     * @param object $request
     * @return $this
     */
    public function setRequest(object $request): static {
        // 验证当前cookie是否为对象
        if ( !$request -> cookie instanceof Cookie) {
            $request -> cookie = app(Cookie::class, [ $request -> cookie ?? [] ], true);
        }
        $this->request = app(Request::class, [], true) -> initializer($request);
        return $this;
    }

    /**
     * 初始化响应数据
     * @param object $response
     * @return $this
     */
    public function setResponse(object $response): static {
        $this->response = app(Response::class, [], true) -> initializer($response);
        return $this;
    }

    /**
     * 初始化控制器对象
     * @return bool
     */
    protected function GenerateControllerService(): bool {
        // TODO: Implement GenerateControllerService() method.
        $this -> RequestController = explode('@', $this->router['action']);

        // 验证控制器类是否存在
        if (!class_exists($this->RequestController[0])) {
            throw new RequestValidateException(message() -> server_error(502, 'Request Object dose not exists'));
        }

        $this->ReflectionClass = new ReflectionClass($this->RequestController[0]);

        // 验证控制器方法是否存在
        if (!$this->ReflectionClass -> hasMethod($this->RequestController[1])) {
            return $this->response -> notFount('Request Method dose not exists');
        }
        return false;
    }

    /**
     * 格式化请求参数
     * @param array $params
     * @return array
     * @throws ValueException
     */
    protected function GenerateRequestQueryParams(array $params = []): array {
        // TODO: Implement GenerateRequestQueryParams() method.
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

    /**
     * 初始化对象类型参数
     * @param array $params
     * @return object
     */
    protected function setInstanceValue(array $params): object {
        // TODO: Implement setInstanceValue() method.

        $keys = array_keys($params);

        $class =
            empty($params[$keys[0]]['class']) || isset($params['class']) ?
                $params['class'] : $params[$keys[0]]['class'];

        // 当类存在时
        if (class_exists($class)) {

            $ref = new \ReflectionClass($class);
            $execute = new Execute();
            $execute -> getReflectorAttributes($ref) -> executeAnnotationLifeProcess('beforeCreate', $ref);
            $object = $ref -> newInstance();

            $container = Container::getInstance();
            if (method_exists($object, '__make')) {
                $container -> invoke([ $object, '__make' ], [ $container, $object ]);
            }

            $args = [ $object ];

            // 执行创建回调以及挂载结束注解
            $execute -> executeAnnotationLifeProcess(['Created', 'beforeMounted'], $ref, $args);
            foreach ($params as $paramName => $paramValue) {
                if (!isset($paramValue['default'])) continue;
                if ($paramValue['type'][0] === 'class') {
                    $paramValue['default'] = $this -> setInstanceValue($paramValue['default']);
                }
                $ref -> getProperty($paramName) -> setValue($object, $paramValue['default']);
            }
            $object = $container -> GenerateClassParameters($ref, $object);
            $execute -> executeAnnotationLifeProcess('Mounted', $ref, $args);
        } else throw new valueException("dataObject: $class IsNull");
        return $object;
    }
}