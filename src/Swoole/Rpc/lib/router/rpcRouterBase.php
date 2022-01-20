<?php


namespace iflow\Swoole\Rpc\lib\router;


use iflow\Router\CheckRule;

class rpcRouterBase extends CheckRule
{
    protected string $routerConfigKey = 'rpc';
}