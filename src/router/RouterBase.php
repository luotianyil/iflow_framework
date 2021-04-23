<?php


namespace iflow\router;


class RouterBase
{

    protected array $router = [];

    /**
     * 获取路由列表
     * @return array
     */
    public function getRouterList() : array
    {
        return config(config('app@router'));
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
     * @return array|mixed
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
                foreach ($value as $k => $v) {
                    $this->setDefaultValue(
                        $router['parameter'][$key][$k]['default'],
                        $key, $v, $param[$key][$v['name']] ?? ''
                    );
                }
            }
        }
        $this->router = $router;
        return $router;
    }

    /**
     * 设置传参值
     * @param $default
     * @param $key
     * @param $value
     * @param $param
     * @param $routerParamDefault
     */
    private function setDefaultValue(&$default, $key, $value, $param)
    {
        $val = match ($value['type']) {
            'array' => array_merge($value['default'], $param ?? []),
            default => function () use ($param, $key, $value) {
                $params = $param ?: null;
                $type = gettype($value['default']);
                if (is_numeric($params) && $type !== 'string') $params = intval($params);
                // 当无 默认值时
                if ($type === 'NULL') return $params;
                if (gettype($params) !== $type) return $value['default'];
                return $params;
            }
        };
        $default = is_object($val) ? call_user_func($val) : $default;
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
