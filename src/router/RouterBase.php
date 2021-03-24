<?php


namespace iflow\router;


class RouterBase
{

    protected array $router = [];

    /**
     * get Router List
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
     * validate Router
     * @param string $url
     * @param string $method
     * @param array $param
     * @return array|bool
     */
    public function validateRouter(string $url = '/', string $method = 'get', array $param = []) : array | bool
    {
        $routerList = $this->getRouterList();
        $router = [];

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
                $router['parameter'][$key]['default'] = match ($value['type']) {
                    'array' => array_merge($value['default'], $param[$value['name']] ?? []),
                    default => function () use ($param, $key, $value) {
                        $params = $param[$key] ?? null;
                        $t = gettype($value['default']);
                        if (is_numeric($params) && $t !== 'string') $params = intval($params);
                        if (gettype($params) !== $t) return $value['default'];
                        return $params;
                    }
                };
                $router['parameter'][$key]['default'] = is_object($router['parameter'][$key]['default']) ? call_user_func($router['parameter'][$key]['default']): $router['parameter'][$key]['default'];
            } else {
                foreach ($value as $k => $v) {
                    $router['parameter'][$key][$k]['default'] = match ($v['type']) {
                        'array' => array_merge($v['default'], $param[$key][$v['name']] ?? []),
                        default => function () use ($param, $key, $v) {
                            $params = $param[$key][$v['name']] ?? null;
                            $t = gettype($v['default']);
                            if (is_numeric($params) && $t !== 'string') $params = intval($params);
                            if (gettype($params) !== $t) return $v['default'];
                            return $params;
                        }
                    };
                    $router['parameter'][$key][$k]['default'] = is_object($router['parameter'][$key][$k]['default']) ? call_user_func($router['parameter'][$key][$k]['default']): $router['parameter'][$key][$k]['default'];
                }
            }
        }
        $this->router = $router;
        return $router;
    }

    /**
     * 验证请求方法
     * @param string $method
     * @param string $rule
     * @return bool
     */
    protected function validateMethod($method = '', $rule = ''): bool
    {
        if ($rule === '*') return true;
        return strtolower($method) === $rule;
    }
}
