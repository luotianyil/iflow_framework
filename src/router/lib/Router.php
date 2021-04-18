<?php


namespace iflow\router\lib;

// 路由方法
use iflow\App;
use iflow\Utils\Tools\StrTools;
use ReflectionClass;
use ReflectionMethod;

#[\Attribute]
class Router
{

    protected App $app;
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
        protected string|array $methods = '',
        protected string $ext = '',
        protected array $parameter = [],
        protected array $options = [],
    ){}

    // 全局类初始化
    public function __make(App $app, ReflectionClass $annotationClass)
    {
        $this->app = $app;
        $this->annotationClass = $annotationClass;

        // 定义路由数据
        $this->routerKey = (string) config('app@router');
        $this->routers = (array) config($this->routerKey);
        $this->bindRouter();
    }

    public function bindRouter()
    {
        $strTools = new StrTools();
        // 获取全部方法
        foreach ($this->annotationClass -> getMethods() as $key) {
            // 获取方法调用的注解
            $annotations = $key -> getAttributes();
            foreach ($annotations as $annotation) {
                if (in_array($annotation -> getName(), $this->routerAttributeNames)) {
                    $parameter = $this->getRouterMethodParameter($key);
                    $routerAnnotation = $annotation -> newInstance();
                    $router = $routerAnnotation -> getRouter(
                        $this->rule ?: $strTools -> unHumpToLower($key -> getName()),
                        "{$this->annotationClass -> getName()}@{$key -> getName()}",
                        $this->options
                    );
                    $router['parameter'] = array_merge($parameter, $router['parameter']);

                    if (empty($this->routers['router'][$this->rule]))
                        $this->routers['router'][$this->rule] = [];

                    // 验证是否存在该路由
                    if (!in_array($router, $this->routers['router'][$this->rule]))
                        $this->routers['router'][$this->rule][] = $router;
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
            if (!$type instanceof \ReflectionType) continue;
            if (class_exists($type -> getName())) {
                $className = $type -> getName();
                $parametersType = new ReflectionClass($className);
                $parametersTypeInstance = $parametersType -> newInstance();
                $this->routers['routerParams'][$className] = $this->routers['routerParams'][$className] ?? [];
                foreach ($parametersType -> getProperties() as $param) {
                    $p = $param -> getName();
                    $defaultValue = $parametersType -> getProperty($p);
                    if ($defaultValue -> isPublic()) {
                        $this->routers['routerParams'][$className][$param -> getName()] = [
                            'type' => app() -> getParameterType($param),
                            'class' => $parametersTypeInstance::class,
                            'name' => $param -> getName(),
                            'default' => $defaultValue -> getDefaultValue()
                        ];
                    }
                }
                $parameter[$name] = [
                    'type' => 'class',
                    'class' => $className
                ];
            } else {
                $parameter[$name] = [
                    'type' => app() -> getParameterType($key),
                    'name' => $name,
                    'default' => $key -> isDefaultValueAvailable() ? $key -> getDefaultValue() : ''
                ];
            }
        }
        return $parameter;
    }

    public function getRouter(string $fatherRouter, string $action = '', array $options = []) : array
    {
        if (is_array($this->methods)) $this->methods = implode("|", $this->methods);
        $methods = explode('|', strtolower($this -> methods));
        return [
            'rule' => str_replace('//', '/', $fatherRouter. '/' .$this->rule),
            'method' => empty($methods[0]) ? ['*'] : $methods,
            'action' => $action,
            'ext' => $this->ext,
            'parameter' => $this->parameter,
            'options' => array_replace_recursive($options, $this->options)
        ];
    }
}