<?php


namespace iflow\router;

use iflow\App;
use iflow\router\lib\request\RequestMapping;
use iflow\router\lib\utils\setRouterRule;
use iflow\Utils\Tools\StrTools;
use ReflectionClass;

#[\Attribute]
class Controller extends RequestMapping
{
    protected App $app;
    protected ReflectionClass $annotationClass;
    protected StrTools $strTools;
    protected setRouterRule $setRouterRule;

    protected string $routerConfigKey = 'app@router';

    // 绑定路由
    protected array $routers = [
        // 路由表
        'router' => [],
        // 路由类参数
        'routerParams' => []
    ];
    protected string $routerKey = '';
    protected array $config = [
        'key' => 'router',
        // 路由前缀列表
        'routerPrefix' => []
    ];

    // 指定域名
    protected array $domain = [];

    // 定义控制器注解
    public function __construct(
        protected string $rule = '',
        protected string $method = '*',
        protected string $ext = '*',
        protected array $parameter = [],
        protected array $options = []
    ){}

    /**
     * 初始化
     * @param App $app
     * @param ReflectionClass $annotationClass
     */
    public function __make(App $app, ReflectionClass $annotationClass) {
        $this->app = $app;
        $this->annotationClass = $annotationClass;

        $this->setRouterConfig();

        // 初始化工具类
        $this->strTools = new StrTools();
        $this->setRouterRule = (
            new setRouterRule($this->config, $this->routers)
        ) -> setStrTools($this->strTools);

        $this -> getControllerClass()
              -> getControllerAction();
    }

    public function setRouterConfig() {
        $config = config($this->routerConfigKey);
        $this->config = array_merge($this->config, $config);

        // 定义路由数据
        $this->routerKey = $this->config['key'];
        $this->routers = array_replace_recursive(config($this->routerKey), $this->routers) ?: $this->routers;
    }

    /**
     * 获取控制器类
     * @return $this
     */
    protected function getControllerClass(): static
    {
        $this->rule = $this->setRouterRule -> getRouterRulePrefix(
            $this->rule ?: $this->strTools -> humpToLower($this->annotationClass -> getShortName())
        );
        $this->domain = $this->setRouterRule -> getDomain($this->annotationClass);
        return $this;
    }

    /**
     * 获取控制器方法
     * @return $this
     */
    protected function getControllerAction(): static
    {
        foreach ($this->annotationClass -> getMethods() as $action) {
            if ($action -> isPublic()) {
                config(
                    $this->setRouterRule
                        -> setParentRule($this->rule)
                        -> getRouter($action, $this->annotationClass, $this->domain),
                    $this->routerKey
                );
            }
        }
        return $this;
    }
}