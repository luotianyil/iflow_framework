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
        return config('router');
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
     * @param $request
     * @return array|bool
     */
    public function validateRouter($request) : array | bool
    {
        $routerList = $this->getRouterList();
        $url = [];
        return $this->getRouterParam($routerList, $url);
    }

    /**
     * 获取单条路由验证数据
     * @param array $routerList
     * @param array $url
     * @return bool|array
     */
    public function getRouterParam(array $routerList = [], array $url = []): array | bool
    {
        foreach ($routerList as $key) {
            if (is_array($key) && empty($key['rule'])) {
                $this->getRouterParam($key, $url);
            } else {
                $url = explode('/', $url);
                $rule = explode('/', $key['rule']);
                $key['param'] = [];
                if (count($rule) === count($url)) {
                    foreach ($url as $k => $v) {
                        $e = preg_replace('/[<|>]/', '', $rule[$k]);
                        $e = explode(':', $e);
                        if ($e[0] === '?') $key['param'][] = $v;
                        else if (sizeof($e) > 1) {
                            if (preg_match('/'.$e[0].'/', $v)) {
                                $key['param'][] = $v;
                            } else return false;
                        }
                    }
                    return $key;
                }
            }
        }
        return false;
    }
}