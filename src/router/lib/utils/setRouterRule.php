<?php


namespace iflow\router\lib\utils;


use iflow\router\lib\request\DeleteMapping;
use iflow\router\lib\request\GetMapping;
use iflow\router\lib\request\HeadMapping;
use iflow\router\lib\request\PatchMapping;
use iflow\router\lib\request\PostMapping;
use iflow\router\lib\request\PutMapping;
use iflow\router\lib\request\RequestMapping;
use iflow\router\lib\Router;
use iflow\Utils\Tools\StrTools;

class setRouterRule
{
    private array $annotations = [
        GetMapping::class,
        PostMapping::class,
        PutMapping::class,
        HeadMapping::class,
        DeleteMapping::class,
        RequestMapping::class,
        Router::class,
        PatchMapping::class
    ];
    protected array $methods = [];
    protected string $parentRule = "";
    protected setRouterParams $setParams;
    protected StrTools $strTools;

    public function __construct(
        // 路由配置
        protected array $config = [],
        // 路由组
        protected array $routers = []
    ){
        $this->setParams = new setRouterParams();
    }

    /**
     * 设置路由配置
     * @param array $config
     * @return static
     */
    public function setConfig(array $config): static
    {
        $this->config = $config;
        return $this;
    }

    /**
     * 设置路由数据
     * @param array $router
     * @return static
     */
    public function setRouter(array $router): static
    {
        $this->routers = $router;
        return $this;
    }

    /**
     * @param string $parentRule
     * @return static
     */
    public function setParentRule(string $parentRule): static
    {
        $this->parentRule = $parentRule;
        return $this;
    }

    /**
     * @param StrTools $strTools
     * @return setRouterRule
     */
    public function setStrTools(StrTools $strTools): static
    {
        $this->strTools = $strTools;
        return $this;
    }

    // 获取路由数据
    public function getRouter(
        \ReflectionMethod $action,
        \ReflectionClass $annotationClass,
        // 控制器类定义的域名
        array $domain
    ) {
        $parameter = $this->setParams
            -> setRouterParams($this->routers['routerParams'])
            -> getRouterMethodParameter($action);

        $this->routers['routerParams'] = array_merge($this->routers['routerParams'], $parameter[1]);
        $parameter = $parameter[0];

        $domain = array_merge($this->getDomain($action), $domain);

        $routers = [];
        if (empty($this->routers['router'][$this->parentRule]))
            $this->routers['router'][$this->parentRule] = [];
        // 获取方法请求方法
        foreach ($this->getActionAnnotations($action) as $actionAnnotation) {
            $routerAnnotation = $actionAnnotation -> newInstance();
            $router = $this->getRouterRule(
                $routerAnnotation,
                "{$annotationClass -> getName()}@{$action -> getName()}",
                $routerAnnotation -> getRule() ?: $this->strTools -> humpToLower($action -> getName()),
                $domain
            );
            $router['parameter'] = array_merge($parameter, $router['parameter']);

            // 验证路由是否存在
            if (count($routers) === 0) {
                $routers[] = $router;
            } else {
                $checkSuccess = false;
                foreach ($routers as $routerKey => &$routerValue) {
                    if ($routerValue['rule'] !== $router['rule']) continue;
                    $routerValue['method'] = array_merge($router['method'], $routerValue['method']);
                    $checkSuccess = true;
                }
                if (!$checkSuccess) $routers[] = $router;
            }
        }

        foreach ($routers as $router) {
            $this->routers['router'][$this->parentRule][] = $router;
        }
        return $this->routers;
    }

    /**
     * 获取路由全部信息
     * @param RequestMapping $mapping | 请求方法对象
     * @param string $action | 控制器类@方法
     * @param string $rule | 请求路由
     * @param array $domain | 所属路由组
     * @return array
     */
    public function getRouterRule(
        RequestMapping $mapping, string $action, string $rule, array $domain
    ): array {
        return [
            'rule' => str_replace('//', '/',
                $this->parentRule . '/' . $rule
            ),
            'method' => $mapping -> getMethod(),
            'action' => $action,
            'ext' => $mapping -> getExt(),
            'parameter' => $mapping -> getParameter(),
            'options' => $mapping -> getOptions(),
            'domain' => $domain
        ];
    }

    /**
     * 获取指定方法注解
     * @param \ReflectionMethod $action
     * @return array
     */
    protected function getActionAnnotations(\ReflectionMethod $action): array
    {
        $actionAnnotations = [];
        // 遍历获取方法注解
        foreach ($this->annotations as $annotation) {
            // 获取方法注解
            $actionAnnotation = $action -> getAttributes($annotation);
            $actionAnnotation = $actionAnnotation[0] ?? '';
            if ($actionAnnotation) $actionAnnotations[] = $actionAnnotation;
        }
        return $actionAnnotations;
    }

    /**
     * 检测路由是否设定前缀
     * @param string $router
     * @return string
     */
    public function getRouterRulePrefix(string $router): string
    {
        $startStr = explode('/', $router)[0];
        preg_match("/^%(.*?)%$/", $startStr, $prefix);
        if (count($prefix) > 1) {
            $router = str_replace($startStr, $this->config['routerPrefix'][$prefix[1]] ?? '', $router);
        }
        return $router;
    }

    /**
     * 获取设定路由域名分组信息
     * @param \ReflectionMethod|\ReflectionClass $ref
     * @return array
     */
    public function getDomain(\ReflectionMethod|\ReflectionClass $ref): array
    {
        $domainAnnotation = $ref -> getAttributes(Domain::class)[0] ?? '';
        if ($domainAnnotation) {
            return ($domainAnnotation -> newInstance()) -> getDomain();
        }
        return [];
    }
}