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
        foreach ($this->router['parameter'] as $routerParamKey => $paramValue) {
            if ($paramValue['type'] === 'class') {
                $this->router['parameter'][$routerParamKey] =
                    $paramValue = $this->routerList['routerParams'][$paramValue['class']];
            }
            if (isset($paramValue['default'])) {
                $this->setDefaultValue(
                    $this->router['parameter'][$routerParamKey]['default'],
                    $routerParamKey, $paramValue, $this->parameters[$paramValue['name']] ?? ''
                );
                continue;
            }

            foreach ($paramValue as $paramsName => $paramsValue) {
                $this->parameters[$routerParamKey][$paramsValue['name']]
                    = $this->parameters[$routerParamKey][$paramsValue['name']] ?? '';

                if ($paramsValue['type'][0] !== 'class') {
                    $this->setDefaultValue(
                        $this->router['parameter'][$routerParamKey][$paramsName]['default'],
                        $routerParamKey, $paramsValue, $this->parameters[$routerParamKey][$paramsValue['name']]
                    );
                    continue;
                }

                $paramsValue['default'] =
                    $this->router['parameter'][$routerParamKey][$paramsName]['default']
                    = $this->routerList['routerParams'][$paramsValue['class']];

                $this->setClassDefaultValue(
                    $this->router['parameter'][$routerParamKey][$paramsName]['default'],
                    $paramsValue,
                    $paramsName,
                    $this->parameters[$routerParamKey][$paramsValue['name']]
                );
            }
        }
        return $this->router;
    }

    /**
     * 绑定类参数
     * @param $router
     * @param $paramsValue
     * @param $paramsName
     * @param $params
     */
    public function setClassDefaultValue(&$router, $paramsValue, $paramsName, $params)
    {
        foreach ($paramsValue['default'] as $paramsName => $paramsValue) {
            $params[$paramsValue['name']] = $params[$paramsValue['name']] ?? '';
            if ($paramsValue['type'][0] === 'class') {
                if (isset($params[$paramsValue['name']])) {
                    $paramsValue['default'] =
                    $router[$paramsName]['default'] = $this->routerList['routerParams'][$paramsValue['class']];
                    $this->setClassDefaultValue(
                        $router[$paramsName]['default'],
                        $paramsValue,
                        $paramsName,
                        $params[$paramsValue['name']]
                    );
                }
            } else {
                $this->setDefaultValue(
                    $router[$paramsName]['default'],
                    $paramsName, $paramsValue, $params[$paramsValue['name']]
                );
            }
        }
    }

    /**
     * 更改路由参数默认值
     * @param $default
     * @param $routerParamKey
     * @param $paramValue
     * @param $param
     * @return false|mixed
     */
    public function setDefaultValue(&$default, $routerParamKey, $paramValue, $param): mixed
    {
        $defaultValue = match ($paramValue['type']) {
            'array' => array_merge($paramValue['default'], $param ?? []),
            default => function () use ($default, $routerParamKey, $paramValue, $param) {
                $params = isset($param) && $param !== '' ? $param : null;
                $paramType = gettype($paramValue['default']);
                if (is_numeric($params) && $paramType !== 'string') $params = intval($params);
                if (is_float($params) && $paramType !== 'string') $params = floatval($params);

                if ($paramType === 'NULL') return $params;
                if (gettype($params) !== $paramType) return $paramValue['default'];

                return $params;
            }
        };
        return $default = is_callable($defaultValue) ? call_user_func($defaultValue) : $default;
    }

}