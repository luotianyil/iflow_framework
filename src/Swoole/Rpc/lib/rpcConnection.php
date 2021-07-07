<?php


namespace iflow\Swoole\Rpc\lib;


use iflow\facade\Cache;
use Swoole\Server;

class rpcConnection
{

    public function onConnect(Server $server, $fd) {}

    public function onClose($server, $fd)
    {
        $config = config('swoole.rpc@server.clientList');

        $cache = Cache::store($config);
        $client = $cache -> get($config['cacheName']);
        unset($client['clientList'][$fd]);
        $cache -> set($config['cacheName'], $client);
        $server -> close($fd);
    }
}