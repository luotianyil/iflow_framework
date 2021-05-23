<?php


namespace iflow\Swoole\Rpc\lib\router;


use iflow\App;
use iflow\router\lib\Router;
use ReflectionClass;

#[\Attribute]
class rpcRouter extends Router
{
    protected string $routerConfigKey = 'app@rpcRouter';
}