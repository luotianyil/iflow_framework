<?php


namespace iflow\Swoole\Rpc\lib\router;


use iflow\router\RouterBase;

class rpcRouterBase extends RouterBase
{

    public function getRouterList(): array
    {
        return config(config('app@rpcRouter'));
    }

}