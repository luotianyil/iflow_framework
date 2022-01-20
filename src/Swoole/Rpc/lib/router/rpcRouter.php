<?php


namespace iflow\Swoole\Rpc\lib\router;

use iflow\Router\Controller;

#[\Attribute]
class rpcRouter extends Controller {
    protected string $routerConfigKey = 'rpc';
}