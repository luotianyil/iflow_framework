<?php


namespace iflow\router\lib\utils;


class bindRequestParams
{
    public function __construct(
        // 当前路由
        protected array $router,
        // 路由列表
        protected array $routerList,
        // 客户端传来参数
        protected array $parameters
    ){}

    /**
     * 绑定路由参数
     * @return array
     */
    public function bindParams(): array
    {
        $routerParams = [];
        foreach ($this->router['parameter'] as $routerParameterKey => $routerParameterValue) {
            $routerParams[$routerParameterKey] = $routerParameterValue;
            if (empty($this->parameters[$routerParameterKey])) continue;
            if ($routerParameterValue['type'][0] !== 'class') {
                $routerParams[$routerParameterKey]['default'] = $this->setDefaultValue(
                    $routerParams[$routerParameterKey],
                    $this->parameters[$routerParameterKey]
                );
                continue;
            }

            // 处理为类的参数
            $routerParams[$routerParameterKey] = $this->setClassDefaultValue(
                $this->routerList['routerParams'][$routerParameterValue['class']],
                $this->parameters[$routerParameterKey]
            );
        }
        $this->router['parameter'] = $routerParams;
        return $this->router;
    }

    /**
     * 绑定类参数
     * @param $routerParameter | 路由参数
     * @param $Params | 前端传递参数
     * @return array
     */
    public function setClassDefaultValue($routerParameter, $Params): array
    {
        $parameters = [];
        foreach ($routerParameter as $classParameterKey => $classParameterValue) {
            $parameters[$classParameterKey] = $classParameterValue;
            if (empty($Params[$classParameterKey])) continue;

            if ($classParameterValue['type'][0] !== 'class') {
                $parameters[$classParameterKey]['default'] = $this->setDefaultValue(
                    $classParameterValue, $Params[$classParameterKey]
                );
                continue;
            }

            // 如果是类 递归操作
            $parameters[$classParameterKey]['default'] = $this->setClassDefaultValue(
                $this->routerList['routerParams'][$classParameterValue['class']],
                $Params[$classParameterKey]
            );
        }
        return $parameters;
    }

    /**
     * 更改路由参数默认值
     * @param $routerParam | 路由参数
     * @param $param | 前端传参
     * @return mixed
     */
    public function setDefaultValue($routerParam, mixed $param): mixed
    {
        if ($routerParam['type'][0] === 'mixed') return $param;

        if ($routerParam['type'][0] === 'array')
            return array_merge($routerParam['default'], is_array($param) ? $param : [$param]) ?? [];

        if (is_numeric($param) && $routerParam['type'][0] !== 'string') return intval($param);
        if (is_float($param) && $routerParam['type'][0] !== 'string') return floatval($param);
        return $param;
    }

}