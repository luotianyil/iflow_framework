<?php


namespace iflow\Swoole\Services\Http\lib;

use iflow\Container\Container;
use iflow\Container\implement\annotation\tools\data\exceptions\ValueException;
use iflow\Container\implement\annotation\traits\Execute;
use iflow\Container\implement\generate\exceptions\InvokeClassException;
use iflow\Container\implement\generate\exceptions\InvokeFunctionException;
use iflow\http\lib\Cookie;
use iflow\Request;
use iflow\Response;
use iflow\Router\CheckRule;
use ReflectionException;


class initializer extends requestTools {

    public function __initializer($request, $response) {
        $this->setRequest($request) -> setResponse($response);
        foreach ($this->runProcess as $key) {
            if (method_exists($this, $key) && call_user_func([$this, $key])) break;
        }
    }

    /**
     * 初始化请求数据
     * @param $request
     * @return $this
     */
    public function setRequest($request): static {
        // 验证当前cookie是否为对象
        if (!$request -> cookie instanceof Cookie) {
            $request -> cookie = app(Cookie::class, [ $request -> cookie ?: [] ]);
        }
        $this->request = app(Request::class, [], true) -> initializer($request);
        return $this;
    }

    // 初始化响应数据
    public function setResponse($response): static {
        $this->response = app(Response::class, [], true) -> initializer($response);
        return $this;
    }

    /**
     * 验证路由
     * @throws ReflectionException
     */
    protected function validateRouter(): bool {
        $this->router = app(CheckRule::class)
            -> setRouterConfigKey('http')
            -> checkRule(
                $this->request -> request_uri,
                $this->request -> request_method,
                $this->request -> params() ?? [],
                $this->request -> getDomain()
            );

        if (!$this->router) {
            return $this->response -> notFount();
        }
        $this->request -> setRouter($this -> router);
        return $this->newInstanceController();
    }

    /**
     * 实例化控制器
     * @return bool
     * @throws ReflectionException
     */
    protected function newInstanceController(): bool {
        $this->requestController = explode('@', $this->router['action']);
        if ($this->validateResponse(class_exists($this->requestController[0]))) return true;
        $this->refController = new \ReflectionClass($this->requestController[0]);
        if ($this->validateResponse($this->refController -> hasMethod($this->requestController[1]))) return true;
        return false;
    }

    /**
     * 执行控制器方法
     * @return bool
     * @throws ReflectionException
     */
    protected function startController(): bool {
        $controller = app($this->refController -> getName(), [ $this->request, $this->response ], true);
        // 执行方法
        return $this->send(app() -> invoke([$controller, $this->requestController[1]], $this->routerBindParams));
    }

    /**
     * 设置 Bean 参数
     * @param array $params
     * @return object
     * @throws ReflectionException|ValueException|InvokeFunctionException|InvokeClassException
     */
    protected function setInstanceValue(array $params): object
    {
        $keys = array_keys($params);

        $class =
            empty($params[$keys[0]]['class']) || isset($params['class']) ?
                $params['class']:$params[$keys[0]]['class'];

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
