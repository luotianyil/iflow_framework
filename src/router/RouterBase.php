<?php


namespace iflow\router;


class RouterBase
{
    /**
     * get Router List
     * @return array
     */
    public function getRouterList() : array
    {
        return config(config('app@router'));
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
    public function getRouterParam(array $routerList = [], string $url = '', string $method = ''): array
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
                    $key['param'] = [];
                    if (count($rule) === count($url)) {
                        $ruleIsSuccess = true;
                        foreach ($url as $k => $v) {
                            $e = preg_replace('/[<|>]/', '', $rule[$k]);
                            $e = explode(':', $e);
                            if (count($e) === 1 && $e[0] !== $v) {
                                $ruleIsSuccess = false;
                                break;
                            }

                            if ($e[0] === '?') $key['param'][] = $v;
                            else if (sizeof($e) > 1) {
                                if (is_string($e[0]) && 0 !== strpos($e[0], '/') && !preg_match('/\/[imsU]{0,4}$/', $e[0])) {
                                    $e[0] = '/^' . $e[0] . '$/';
                                }
                                if (preg_match($e[0], $v)) {
                                    $key['param'][] = $v;
                                } else {
                                    $ruleIsSuccess = false;
                                    break;
                                }
                            }
                        }
                        if ($ruleIsSuccess) {
                            return $this->validateMethod($method, $key['method']) ? $key :[];
                        }
                    }
                }
            }
        }
        return $router;
    }

    public function bindParam(array $router, array $param, array $routerList): array
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
                        if (gettype($params) !== gettype($value['default'])) return $value['default'];
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
                            if (gettype($params) !== gettype($v['default'])) return $v['default'];
                            return $params;
                        }
                    };

                    $router['parameter'][$key][$k]['default'] = is_object($router['parameter'][$key][$k]['default']) ? call_user_func($router['parameter'][$key][$k]['default']): $router['parameter'][$key][$k]['default'];
                }
            }
        }
        return $router;
    }

    public function validateMethod($method = '', $rule = ''): bool
    {
        if ($rule === '*') return true;
        return strtolower($method) === $rule;
    }
}
