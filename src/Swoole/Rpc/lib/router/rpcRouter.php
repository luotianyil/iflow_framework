<?php


namespace iflow\Swoole\Rpc\lib\router;


use iflow\App;
use iflow\router\lib\Router;
use ReflectionClass;

#[\Attribute]
class rpcRouter extends Router
{

    public function __make(App $app, ReflectionClass $annotationClass)
    {
        $this->app = $app;
        $this->fatherRouter = $this->rule;
        $this->annotationClass = $annotationClass;

        $this->routerAttributeNames[] = rpcRouter::class;

        // 定义路由数据
        $this->routerKey = config('app@rpcRouter');
        $this->routers = config($this->routerKey);
        $this->bindRouter();
    }

}