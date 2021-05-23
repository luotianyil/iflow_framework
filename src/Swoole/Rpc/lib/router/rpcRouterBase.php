<?php


namespace iflow\Swoole\Rpc\lib\router;


use iflow\router\RouterBase;

class rpcRouterBase extends RouterBase
{
    protected string $routerConfigKey = 'app@rpcRouter';
}