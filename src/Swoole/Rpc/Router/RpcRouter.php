<?php

namespace iflow\Swoole\Rpc\Router;

use iflow\Router\CheckRule;

class RpcRouter extends CheckRule {
    protected string $routerConfigKey = 'rpc';
}