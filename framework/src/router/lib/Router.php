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
        protected string $methods = 'get',
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
                    array_merge($parameter, $router['parameter']);
                    $this->routers[$this->fatherRouter][] = $router;

                }
            }
        }
        config($this->routers, $this->routerKey);
    }

    /**
     * 获取路由方法 参数
     * @param ReflectionMethod $methond
     * @return array
     * @throws \ReflectionException
     */
    public function getRouterMethodParameter(ReflectionMethod $methond): array
    {
        $parameters = $methond -> getParameters();

        $parameter = [];

        // 遍历 方法参数
        foreach ($parameters as $key) {
            $type = $key -> getType();
            $name = $key -> getName();
            assert($type instanceof \ReflectionType);
            if (class_exists($type -> getName())) {
                $parameter[$name] = (new ReflectionClass($type -> getName())) -> getProperties();
            } else {
                $parameter[$name] = [
                    'type' => $type -> getName(),
                    'name' => $name,
                ];
            }
        }
        return $parameter;
    }

    public function getRouter(string $fatherRouter, string $action = '') : array
    {
        return [
            'rule' => $fatherRouter.$this->rule,
            'methods' => $this->methods?:'get',
            'action' => $action,
            'ext' => $this->ext,
            'parameter' => $this->parameter,
            'options' => $this->options,
        ];
    }
}