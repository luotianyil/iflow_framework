<?php


namespace iflow\router\lib\utils;


use iflow\router\exception\RouterParamsException;

class bindRequestParams
{
    public function __construct(
        // 当前路由
        protected array $router,
        // 路由列表
        protected array $routerList,
        // 客户端传来参数
        protected array $parameters
    ) {}

    /**
     * 绑定路由参数
     * @return array
     * @throws RouterParamsException
     */
    public function bindParams(): array
    {
        $routerParams = [];
        foreach ($this->router['parameter'] as $routerParameterKey => $routerParameterValue) {
            $routerParams[$routerParameterKey] = $routerParameterValue;
            if (!isset($this->parameters[$routerParameterKey]) && empty($this->parameters[$routerParameterKey])) continue;
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
     * @throws RouterParamsException
     */
    public function setClassDefaultValue($routerParameter, $Params): array
    {
        $parameters = [];
        foreach ($routerParameter as $classParameterKey => $classParameterValue) {
            $parameters[$classParameterKey] = $classParameterValue;
            if (!isset($Params[$classParameterKey]) && empty($Params[$classParameterKey])) continue;

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
     * @throws RouterParamsException
     */
    public function setDefaultValue($routerParam, mixed $param): mixed
    {
        if ($routerParam['type'][0] === 'mixed') return $param;
        if (empty($param) && !is_numeric($param)) return $routerParam['default'];


        if ($routerParam['type'][0] === 'array')
            return array_merge($routerParam['default'], is_array($param) ? $param : [$param]) ?? [];

        // 检测是否为数值
        if (is_numeric($param) && !in_array('string', $routerParam['type'])) {
            $value = floatval($param);
            if (!$value && $param != $value) throw new RouterParamsException('QueryParam Name: '. $routerParam['name']. ' miss', 401);
            return $value;
        }

        // 检测是否为bool
        if (in_array('bool', $routerParam['type'])) {
            if ($param === 'false' || $param === '0') return false;
            return boolval($param);
        }
        return $param;
    }
}
