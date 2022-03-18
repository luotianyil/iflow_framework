<?php

namespace iflow\Swoole\Rpc\Router;

use Attribute;
use iflow\Router\Controller;

#[Attribute(Attribute::TARGET_CLASS)]
class RpcController extends Controller {
    protected string $routerConfigKey = 'rpc';
}