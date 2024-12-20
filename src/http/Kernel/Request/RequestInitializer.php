<?php

namespace iflow\http\Kernel\Request;

use iflow\Container\Container;
use iflow\Container\implement\annotation\exceptions\AttributeTypeException;
use iflow\Container\implement\annotation\tools\data\exceptions\ValueException;
use iflow\Container\implement\annotation\traits\Execute;
use iflow\Container\implement\generate\exceptions\InvokeClassException;
use iflow\Container\implement\generate\exceptions\InvokeFunctionException;
use iflow\exception\Adapter\ErrorException;
use iflow\http\Kernel\Exception\RequestValidateException;
use iflow\http\Adapter\Cookie;
use iflow\initializer\Error;
use iflow\Request;
use iflow\Response;
use ReflectionClass;
use ReflectionException;

class RequestInitializer extends RequestVerification {

    /**
     * 请求回调事件
     * @param object|null $request
     * @param object|null $response
     * @param float $startTime
     * @return RequestVerification|bool
     * @throws \Throwable
     */
    public function trigger(object $request = null, object $response = null, float $startTime = 0.00): RequestVerification|bool {
        try {
            app() -> setStartTimes($startTime);
            $request->server['path_info'] = $request->server['path_info'] ?? $request -> server['request_uri'];
            if ($request->server['path_info'] == '/favicon.ico' || $request->server['request_uri'] == '/favicon.ico') {
                $file = config('app@favicon') ?: '';
                if (file_exists($file)) return $response->sendfile($file);
                return true;
            }

            if ($this -> setRequest($request) -> setResponse($response, $startTime)
                -> triggerRequestHook('RequestInitializeHook', $this->request, $response)) {
                foreach ($this->RunProcessMethods as $key) {
                    if (method_exists($this, $key) && call_user_func([$this, $key])) break;
                }
            }
        } catch (\Throwable $exception) {
            return app(Error::class) -> appHandler($exception);
        }

        return $this;
    }


    /**
     * 返回响应内容
     * @return bool
     * @throws InvokeClassException
     * @throws InvokeFunctionException
     * @throws ErrorException|AttributeTypeException
     */
    protected function ReturnsResponseBody(): bool {
        // 执行方法
        $controller = app() -> invokeClass($this -> ReflectionClass -> getName(), [ $this->request, $this->response ]);
        return $this->send(app() -> invoke([$controller, $this->RequestController[1]], $this->RequestQueryParams));
    }

    /**
     * 初始化请求数据
     * @param object $request
     * @return $this
     * @throws InvokeClassException|InvokeFunctionException|AttributeTypeException
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
     * @param float $startTime
     * @return $this
     * @throws AttributeTypeException
     * @throws InvokeClassException
     * @throws InvokeFunctionException
     */
    public function setResponse(object $response, float $startTime = 0.00): static {
        $this->response = app(Response::class, [], true) -> initializer($response);
        $this->response -> startTime = $startTime;
        return $this;
    }

    /**
     * 初始化控制器对象
     * @return bool
     * @throws InvokeClassException
     */
    protected function GenerateControllerService(): bool {
        // TODO: Implement GenerateControllerService() method.
        $this -> RequestController = explode('@', $this->router['action']);

        // 验证控制器类是否存在
        if (!class_exists($this->RequestController[0])) {
            throw new RequestValidateException(message()
                -> setIsRest()
                -> server_error(502, 'Request Object dose not exists')
            );
        }

        $this->ReflectionClass = new ReflectionClass($this->RequestController[0]);

        // 验证控制器方法是否存在
        if (!$this->ReflectionClass -> hasMethod($this->RequestController[1])) {
            return $this->response
                -> notFount('Request Method dose not exists');
        }
        return false;
    }

    /**
     * 格式化请求参数
     * @param array $params
     * @return array
     * @throws InvokeClassException
     * @throws InvokeFunctionException
     * @throws ValueException
     * @throws ReflectionException|AttributeTypeException
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
     * @throws ValueException
     * @throws ReflectionException
     * @throws InvokeClassException
     * @throws InvokeFunctionException
     * @throws AttributeTypeException
     */
    protected function setInstanceValue(array $params): object {
        // TODO: Implement setInstanceValue() method.

        $keys = array_keys($params);

        $class = empty($params[$keys[0]]['class']) || isset($params['class'])
                ? $params['class'] : $params[$keys[0]]['class'];

        $container = Container::getInstance();
        $ref = new \ReflectionClass($class);

        // 如果是接口类型则从容器中实例化对象
        if ($ref -> isInterface()) {
            return $container -> make($class);
        }

        // 如果 容器内存在当前对象
        if ($container -> has($class)) return $container -> get($class);

        $execute = new Execute();
        $execute -> getReflectorAttributes($ref) -> executeAnnotationLifeProcess('beforeCreate', $ref);
        $object = $ref -> newInstance();

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
        return $object;
    }
}