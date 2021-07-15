<?php


namespace iflow\router;


use iflow\router\exception\RouterNotFoundException;
use iflow\router\lib\utils\bindRequestParams;
use iflow\router\lib\utils\checkRouter;

class checkRule
{

    protected array $router = [];
    protected array $routerList;
    protected checkRouter $checkRouter;

    protected string $routerConfigKey = 'app@router';

    // 当前请求参数
    protected array $parameters = [];

    /**
     * 获取路由列表
     * @return array
     */
    public function getRouterList() : array
    {
        $router = config($this -> routerConfigKey);
        return $this->routerList = config($router['key']);
    }

    /**
     * 获取当前请求 路由数据
     * @return array
     */
    public function getRouter(): array
    {
        return $this->router;
    }


    // 验证路由
    public function checkRule(
        string $url = "/",
        string $method = 'get',
        array $param = []
    ): array|bool {
        $routerList = $this->getRouterList();
        $this->checkRouter = new checkRouter();

        $this->parameters = $param;

        $router = [];
        if (empty($routerList['router'])) return $router;

        foreach ($routerList as $rule) {
            $router = $this->check($rule, $url, $method);
            if ($router) break;
        }
        // 验证通过绑定参数
        return $router ? $this->bindParam($router) : throw new RouterNotFoundException();
    }

    public function check(array $ruleAll, string $url, string $method): array|bool
    {
        $router = [];
        foreach ($ruleAll as $ruleKey => $rule) {
            if (is_array($rule) && empty($rule['rule'])) {
                // 验证路由
                if (!str_starts_with(ltrim($url, '/'), ltrim($ruleKey, '/'))) {
                    continue;
                }
                $router = $this->check($rule, $url, $method);
            } else if (is_array($rule)) {
                $router = $this->checkRouter -> check(
                    $rule, $url, $method
                );
            }
            if ($router) return $router;
        }
        return [];
    }

    // 绑定参数
    public function bindParam(array $router): array
    {
        if (count($this->parameters) === 0) return $router;
        return (new bindRequestParams(
            $router,
            $this->routerList,
            $this->parameters
        )) -> bindParams();
    }
}