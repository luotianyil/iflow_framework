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

    private array $config = [
        'key' => 'router',
        // 路由前缀列表
        'routerPrefix' => []
    ];

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

        $config = config('app@router');

        // 验证数据类型 兼容以前版本
        if (is_string($config)) {
            $this->config['key'] = $config;
        } else {
            $this->config = array_merge($this->config, $config);
        }

        // 定义路由数据
        $this->routerKey = $this->config['key'];
        $this->routers = config($this->routerKey);
        $this->bindRouter();
    }

    /**
     * 获取类方法中所有路由
     * @throws \ReflectionException
     */
    public function bindRouter()
    {
        $strTools = new StrTools();

        // 初始化类 路由数据
        $this->rule = $this->routerPrefix($this->rule ?: $strTools -> humpToLower($this->annotationClass -> getName()));

        // 获取全部方法
        foreach ($this->annotationClass -> getMethods() as $key) {
            // 获取方法调用的注解
            $annotations = $key -> getAttributes(__CLASS__);
            $annotation = $annotations[0] ?? '';
            if ($annotation) {
                $parameter = $this->getRouterMethodParameter($key);
                $routerAnnotation = $annotation -> newInstance();
                $router = $routerAnnotation -> setConfig($this->config) -> getRouter(
                    $this->rule,
                    "{$this->annotationClass -> getName()}@{$key -> getName()}",
                    $strTools -> humpToLower($key -> getName()),
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
            // 当无法获取类型时 即为 mixed
            if (!$type instanceof \ReflectionType) {
                $parameter[$name] = [
                    'type' => ['mixed'],
                    'name' => $name,
                    'default' => $this->getTypesDefault($key, ['mixed'])
                ];
                continue;
            }
            if (class_exists($type -> getName())) {
                $className = $type -> getName();
                $parametersType = new ReflectionClass($className);
                $parametersTypeInstance = $parametersType -> newInstance();
                $this->routers['routerParams'][$className] = $this->routers['routerParams'][$className] ?? [];

                // 遍历方法内 参数
                foreach ($parametersType -> getProperties() as $param) {
                    $p = $param -> getName();
                    $defaultValue = $parametersType -> getProperty($p);
                    if ($defaultValue -> isPublic()) {
                        // 参数类型
                        $t = app() -> getParameterType($param);
                        $this->routers['routerParams'][$className][$param -> getName()] = [
                            'type' => $t,
                            'class' => $parametersTypeInstance::class,
                            'name' => $param -> getName(),
                            'default' => $this->getTypesDefault($param, $t)
                        ];
                    }
                }
                $parameter[$name] = [
                    'type' => 'class',
                    'class' => $className
                ];
            } else {
                $t = app() -> getParameterType($key);
                $parameter[$name] = [
                    'type' => $t,
                    'name' => $name,
                    'default' => $this->getTypesDefault($key, $t)
                ];
            }
        }
        return $parameter;
    }

    /**
     * 获取方法路由
     * @param string $fatherRouter
     * @param string $action 执行方法
     * @param string $rule   执行路由
     * @param array $options 其他参数
     * @return array
     */
    public function getRouter(string $fatherRouter, string $action = '', $rule = '', array $options = []) : array
    {
        if (is_array($this->methods)) $this->methods = implode("|", $this->methods);
        $methods = explode('|', strtolower($this -> methods));

        $rule = $this->rule ?: $rule;

        return [
            'rule' => str_replace('//', '/', $fatherRouter. '/' .(
                    $rule ?: throw new \Error('Router rule is empty')
                )
            ),
            'method' => empty($methods[0]) ? ['*'] : $methods,
            'action' => $action,
            'ext' => $this->ext,
            'parameter' => $this->parameter,
            'options' => array_replace_recursive($options, $this->options)
        ];
    }

    /**
     * 检测参数是否有默认值
     * @param \ReflectionProperty|\ReflectionParameter $param
     * @param string|array $type
     * @return mixed
     * @throws \ReflectionException
     */
    private function getTypesDefault(\ReflectionProperty|\ReflectionParameter $param, string|array $type): mixed
    {
        $isDefault = $param instanceof \ReflectionProperty ? $param -> isDefault() : $param -> isDefaultValueAvailable();
        $default = $isDefault ? $param -> getDefaultValue(): '';
        if ($default !== '') return $default;
        $type = is_string($type) ? [$type] : $type;

        // 如果是类 即返回对象
        if (class_exists($type[0])) return $this->app -> make($type[0], isNew: true);
        // 如果是 其他类型
        $default = match ($type[0]) {
            'mixed', 'string' => '',
            'int' => 0,
            'array' => [],
            'bool' => true,
            'float' => 0.00
        };
        return $default;
    }

    /**
     * 检测路由是否使用前缀
     * @param string $router
     * @return string
     */
    private function routerPrefix(string $router): string
    {
        $startStr = explode('/', $router)[0];
        preg_match("/^%(.*?)%$/", $startStr, $prefix);
        if (count($prefix) > 1) {
            $router = str_replace($startStr, $this->config['routerPrefix'][$prefix[1]] ?? '', $router);
        }
        return $router;
    }

    /**
     * @param array $config
     * @return static
     */
    public function setConfig(array $config): static
    {
        $this->config = $config;
        return $this;
    }
}