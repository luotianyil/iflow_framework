<?php


namespace iflow\router\lib;

// 路由方法
use iflow\App;
use ReflectionClass;
use ReflectionMethod;
use function GuzzleHttp\Psr7\str;

#[\Attribute]
class Router
{

    protected App $app;

    // 类 地址
    protected string $fatherRouter = '';
    protected ReflectionClass $annotationClass;

    // routerAttributeNames
    protected array $routerAttributeNames = [
        Router::class
    ];

    // 绑定路由
    protected array $routers = [
        'router' => [],
        'routerParams' => []
    ];

    protected string $routerKey = '';

    public function __construct(
        protected string $rule = '',
        protected string $methods = '',
        protected string $ext = '',
        protected array $parameter = [],
        protected array $options = [],
    ){}

    // 全局类初始化
    public function __make(App $app, ReflectionClass $annotationClass)
    {
        $this->app = $app;
        $this->fatherRouter = $this->rule;
        $this->annotationClass = $annotationClass;

        // 定义路由数据
        $this->routerKey = (string) config('app@router');
        $this->routers = (array) config($this->routerKey);
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
                if (in_array($k -> getName(), $this->routerAttributeNames)) {
                    $k = $k -> newInstance();
                    $router = $k -> getRouter($this->fatherRouter, "{$this->annotationClass -> getName()}@{$key -> getName()}", $this->options);
                    $router['parameter'] = array_merge($parameter, $router['parameter']);
                    $this->routers['router'][$this->fatherRouter][] = $router;
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
            if (class_exists($type -> getName())) {
                $className = $type -> getName();
                $parametersType = new ReflectionClass($className);
                $parametersTypeInstance = $parametersType -> newInstance();
                $this->routers['routerParams'][$className] = $this->routers['routerParams'][$className] ?? [];
                foreach ($parametersType -> getProperties() as $param) {
                    $p = $param -> getName();
                    $defaultValue = $parametersType -> getProperty($p);
                    $this->routers['routerParams'][$className][$param -> getName()] = [
                        'type' => $defaultValue -> getType() -> getName(),
                        'class' => $parametersTypeInstance::class,
                        'name' => $param -> getName(),
                        'default' => $defaultValue -> getDefaultValue()
                    ];
                }
                $parameter[$name] = [
                    'type' => 'class',
                    'class' => $className
                ];
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

    public function getRouter(string $fatherRouter, string $action = '', array $options = []) : array
    {
        return [
            'rule' => $fatherRouter. '/' . ltrim($this->rule, '/'),
            'method' => $this->methods !== ''? strtolower($this->methods) :'*',
            'action' => $action,
            'ext' => $this->ext,
            'parameter' => $this->parameter,
            'options' => array_replace_recursive($options, $this->options)
        ];
    }
}