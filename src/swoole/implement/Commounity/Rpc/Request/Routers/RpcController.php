<?php

namespace iflow\swoole\implement\Commounity\Rpc\Request\Routers;

use Attribute;
use iflow\Router\Controller;

#[Attribute(Attribute::TARGET_CLASS)]
class RpcController extends Controller {

    protected string $routerConfigKey = 'rpc';

}
