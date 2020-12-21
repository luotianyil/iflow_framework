<?php


namespace iflow\router\lib;

// 路由方法
use iflow\App;
use JetBrains\PhpStorm\ArrayShape;
use ReflectionClass;

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
        protected array $parameter = []
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
            foreach ($annotations as $k) {
                if ($k -> getName() === Router::class) {
                    $k = $k -> newInstance();
                    $this->routers[$this->fatherRouter][] =
                        $k -> getRouter($this->fatherRouter, "{$this->annotationClass -> getName()}@{$key -> getName()}");
                }
            }
        }
        config($this->routers, $this->routerKey);
    }

    #[ArrayShape(['rule' => "string", 'methods' => "string", 'ext' => "string", 'parameter' => "array"])]
    public function getRouter(string $fatherRouter, string $action = '') : array
    {
        return [
            'rule' => $fatherRouter.'/'.$this->rule,
            'methods' => $this->methods?:'get',
            'action' => $action,
            'ext' => $this->ext,
            'parameter' => $this->parameter,
        ];
    }
}