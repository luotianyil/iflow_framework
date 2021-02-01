<?php


namespace iflow\Swoole\Rpc\lib;


use iflow\facade\Config;

class rpcConnection
{

    public function onConnect($server, $fd)
    {}

    public function onClose($server, $fd)
    {
        $config = config('rpc@server.clientList');
        $client = Config::getConfigFile($config['path'] . $config['name']);
        unset($client['clientList'][$fd]);
        Config::saveConfigFile($client,
            $config['name'],
            $config['path']
        );
        $server -> close($fd);
    }

}