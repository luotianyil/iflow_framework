<?php

namespace iflow\Swoole\Rpc\Server;

use iflow\facade\Cache;
use Swoole\Server;
use function config;

class RpcConnection {

    public function onConnect(Server $server, int $fd) {}

    /**
     * 当客户端触 关闭当前链接时 将当前数据移出服务列表
     * @param $server
     * @param int $fd
     * @return void
     * @throws \Exception
     */
    public function onClose($server, int $fd) {
        $config = config('swoole.rpc@server.clientList');

        $cache = Cache::store($config);
        $client = $cache -> get($config['cacheName']);
        unset($client['clientList'][$fd]);
        $cache -> set($config['cacheName'], $client);
        $server -> close($fd);
    }
}