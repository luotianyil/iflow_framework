<?php


namespace iflow\router;


class RouterBase
{

    protected array $router = [];

    protected array $routerList;

    /**
     * 获取路由列表
     * @return array
     */
    public function getRouterList() : array
    {

        $router = config('app@router');
        // 验证数据类型 兼容以前版本
        if (is_array($router)) {
            $router = $router['key'] ?? 'router';
        }
        return $this->routerList = config($router);
    }

    /**
     * 获取当前请求 路由数据
     * @return array
     */
    public function getRouter(): array
    {
        return $this->router;
    }

    /**
     * save Router List
     * @return bool
     */
    public function saveRouterList() : bool
    {
        return true;
    }

    /**
     * 验证路由
     * @param string $url
     * @param string $method
     * @param array $param
     * @return array|bool
     */
    public function validateRouter(string $url = '/', string $method = 'get', array $param = []) : array | bool
    {
        $routerList = $this->getRouterList();
        $router = [];

        if (!$routerList['router']) return $router;
        foreach ($routerList['router'] as $key) {
            $router = $this->getRouterParam($key, $url, $method);
            if ($router) break;
        }
        return $router ? $this->bindParam($router, $param, $routerList) : $router;
    }

    /**
     * 获取单条路由验证数据
     * @param array $routerList
     * @param string $url
     * @param string $method
     * @return array
     */
    protected function getRouterParam(array $routerList = [], string $url = '', string $method = ''): array
    {
        $router = [];
        foreach ($routerList as $key) {
            if (is_array($key) && empty($key['rule'])) {
                $router = $this->getRouterParam($key, $url, $method);
            } else {
                if ($url === $key['rule']) {
                    return $this->validateMethod($method, $key['method']) ? $key :[];
                } else {
                    $url = is_array($url)? $url: explode('/', trim($url, '/'));
                    $rule = explode('/', trim($key['rule'], '/'));
                    $router = $this->regxRouter($url, $rule, $method, $key);
                    if ($router) break;
                }
            }
        }
        return $router;
    }

    /**
     * 正则路由验证
     * @param array $url
     * @param array $rule
     * @param string $method
     * @param $key
     * @return mixed
     */
    protected function regxRouter(array $url,array $rule, string $method, $key): mixed
    {
        // 验证url 与 路由长度 是否一致
        if (count($rule) === count($url)) {
            $ruleIsSuccess = true;

            // 验证 url 与 路由 字段是否一致，且验证 是否为正则路由
            foreach ($url as $k => $v) {
                $e = preg_replace('/[<|>]/', '', $rule[$k]);
                $e = explode(':', $e);
                if (count($e) === 1 && $e[0] !== $v) {
                    $ruleIsSuccess = false;
                    break;
                }

                // 验证值是否能为空
                if ($e[0] === '?') {
                    $key['parameter'][$e[1]] = [
                        'type' => '',
                        'name' => $e[1],
                        'default' => $v
                    ];
                } else if (sizeof($e) > 1) {
                    // 正则验证
                    if (is_string($e[0]) && 0 !== strpos($e[0], '/') && !preg_match('/\/[imsU]{0,4}$/', $e[0])) {
                        $e[0] = '/^' . $e[0] . '$/';
                    }
                    if (preg_match($e[0], $v)) {
                        $key['parameter'][$e[1]] = [
                            'type' => '',
                            'name' => $e[1],
                            'default' => $v
                        ];
                    } else {
                        $ruleIsSuccess = false;
                        break;
                    }
                }
            }

            // 路由验证成功 验证 请求方法
            if ($ruleIsSuccess) {
                return $this->validateMethod($method, $key['method']) ? $key :[];
            }
        }
        return [];
    }

    /**
     * 路由绑定参数
     * @param array $router
     * @param array $param
     * @param array $routerList
     * @return array
     */
    protected function bindParam(array $router, array $param, array $routerList): array
    {
        // 遍历路由变量
        foreach ($router['parameter'] as $key => $value) {
            if ($value['type'] === 'class') {
                $router['parameter'][$key] =
                $value = $routerList['routerParams'][$value['class']];
            }

            if (isset($value['default'])) {
                $this->setDefaultValue(
                    $router['parameter'][$key]['default'],
                    $key, $value, $param[$value['name']] ?? ''
                );
            } else {
                foreach ($value as $paramsName => $paramsValue) {
                    if ($paramsValue['type'][0] === 'class') {
                        // 参数为 类时
                        $paramsValue['default'] =
                        $router['parameter'][$key][$paramsName]['default'] = $routerList['routerParams'][$paramsValue['class']];
                        $this->setClassDefaultValue(
                            $router['parameter'][$key][$paramsName]['default'],
                            $paramsValue,
                            $paramsName,
                            $param[$key][$paramsValue['name']] ?? ''
                        );
                    } else {
                        $this->setDefaultValue(
                            $router['parameter'][$key][$paramsName]['default'],
                            $key, $paramsValue, $param[$key][$paramsValue['name']] ?? ''
                        );
                    }
                }
            }
        }
        $this->router = $router;
        return $router;
    }

    /**
     * 递归类参数
     * @param $router
     * @param $value
     * @param $paramsName
     * @param $params
     */
    private function setClassDefaultValue(&$router, $value, $paramsName, $params)
    {
        foreach ($value['default'] as $paramsName => $paramsValue) {
            if ($paramsValue['type'][0] === 'class') {
                if (isset($params[$paramsValue['name']])) {
                    $paramsValue['default'] =
                    $router[$paramsName]['default'] = $this->routerList['routerParams'][$paramsValue['class']];
                    $this->setClassDefaultValue(
                        $router[$paramsName]['default'],
                        $paramsValue,
                        $paramsName,
                        $params[$paramsValue['name']] ?? ''
                    );
                }
            } else {
                $this->setDefaultValue(
                    $router[$paramsName]['default'],
                    $paramsName, $paramsValue, $params[$paramsValue['name']] ?? ''
                );
            }
        }
    }

    /**
     * 设置传参值
     * @param $default
     * @param $key
     * @param $value
     * @param $param
     * @return mixed
     */
    private function setDefaultValue(&$default, $key, $value, $param): mixed
    {
        $val = match ($value['type']) {
            'array' => array_merge($value['default'], $param ?? []),
            default => function () use ($default, $key, $value, $param) {
                // 验证参数是否存在或是否为空
                $params = isset($param) && $param !== "" ? $param : null;
                $type = gettype($value['default']);
                if (is_numeric($params) && $type !== 'string') $params = intval($params);
                // 当无 默认值时
                if ($type === 'NULL') return $params;
                if (gettype($params) !== $type) return $value['default'];
                return $params;
            }
        };
        return $default = is_object($val) ? call_user_func($val) : $default;
    }

    /**
     * 验证请求方法
     * @param string $method
     * @param array $rule
     * @return bool
     */
    protected function validateMethod($method = '', array $rule = []): bool
    {
        if (in_array('*', $rule)) return true;
        return in_array(strtolower($method), $rule);
    }
}
