<?php


namespace iflow\router\lib;

// 路由方法
use iflow\App;
use ReflectionClass;
use ReflectionMethod;

#[\Attribute]
class Router
{

    protected App $app;

    // 类 地址
    protected string $fatherRouter = '';
    protected ReflectionClass $annotationClass;

    // 绑定路由
    protected array $routers = [];
    protected string $routerKey = '';

    public function __construct(
        protected string $rule = '',
        protected string $methods = '',
        protected string $ext = '',
        protected array $parameter = [],
        protected array $options = [],
    )
    {}

    // 全局类初始化
    public function __make(App $app, ReflectionClass $annotationClass)
    {
        $this->app = $app;
        $this->fatherRouter = $this->rule;
        $this->annotationClass = $annotationClass;

        // 定义路由数据
        $this->routerKey = config('app@router');
        $this->routers = config($this->routerKey);
        $this->bindRouter();
    }

    public function bindRouter()
    {
        // 获取全部方法
        foreach ($this->annotationClass -> getMethods() as $key) {
            // 获取方法调用的注解
            $annotations = $key -> getAttributes();
            $parameter = $this->getRouterMethodParameter($key);
            foreach ($annotations as $k) {
                if ($k -> getName() === Router::class) {
                    $k = $k -> newInstance();
                    $router = $k -> getRouter($this->fatherRouter, "{$this->annotationClass -> getName()}@{$key -> getName()}");
                    $router['parameter'] = array_merge($parameter, $router['parameter']);
                    $this->routers[$this->fatherRouter][] = $router;
                }
            }
        }
        config($this->routers, $this->routerKey);
    }

    /**
     * 获取路由方法 参数
     * @param ReflectionMethod $method
     * @return array
     * @throws \ReflectionException
     */
    public function getRouterMethodParameter(ReflectionMethod $method): array
    {
        $parameters = $method -> getParameters();
        $parameter = [];

        // 遍历 方法参数
        foreach ($parameters as $key) {
            $type = $key -> getType();
            $name = $key -> getName();
            assert($type instanceof \ReflectionType);
            if (class_exists($type -> getName())) {
                $parametersType = new ReflectionClass($type -> getName());
                $parametersTypeInstance = $parametersType -> newInstance();
                foreach ($parametersType -> getProperties() as $param) {
                    $p = $param -> getName();
                    $parameter[$name][] = [
                        'type' => gettype($parametersTypeInstance -> $p),
                        'class' => $parametersTypeInstance::class,
                        'name' => $param -> getName(),
                        'default' => $parametersTypeInstance -> $p
                    ];
                }
            } else {
                $parameter[$name] = [
                    'type' => $type -> getName(),
                    'name' => $name,
                    'default' => $key -> isDefaultValueAvailable() ? $key -> getDefaultValue() : ''
                ];
            }
        }
        return $parameter;
    }

    public function getRouter(string $fatherRouter, string $action = '') : array
    {
        return [
            'rule' => $fatherRouter.$this->rule,
            'method' => $this->methods !== ''? strtolower($this->methods) :'*',
            'action' => $action,
            'ext' => $this->ext,
            'parameter' => $this->parameter,
            'options' => $this->options,
        ];
    }
}