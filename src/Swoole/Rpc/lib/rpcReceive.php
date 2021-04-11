<?php


namespace iflow\Swoole\Rpc\lib;

use iflow\facade\Cache;
use iflow\facade\Config;
use iflow\Swoole\Services\Http\lib\initializer;
use Swoole\Server;

class rpcReceive extends initializer
{

    public array $router;
    public int $fd;
    protected Server $server;
    protected ?array $clientList = [];
    protected mixed $config = [];

    public function handle(Server $server, $fd, $reactor_id, $data)
    {
        $this->server = $server;
        $this->fd = $fd;
        $this->config = config('swoole.rpc@server');
        $info = json_decode($data, true);

        $this->clientList = Cache::store($this->config['clientList'])
                            -> get($this->config['clientList']['cacheName']);

        if ($info) {
            if (isset($info['isClientConnection']) && isset($info['client_name'])) {
                return $this -> send(rpc($info['client_name'], $info['request_uri'], $info));
            }
            if ($this->addClient($info, $fd)) {
                $this -> send($info);
            }
            return true;
        }
        return true;
    }

    public function addClient($info, $fd)
    {
        if (!empty($info['initializer']) && $info['initializer'] == 1) {
            if (isset($this->clientList[$fd])) {
                return true;
            }
            if (empty($this->clientList['clientList'])) $this->clientList['clientList'] = [];
            $info['status'] = 1;
            $info['fd'] = $fd;
            $this->clientList['clientList'][$fd] = $info;
            Cache::store($this->config['clientList'])
                -> set($this->config['clientList']['cacheName'], $this->clientList);
            return true;
        }
        return false;
    }

    protected function send($response): bool
    {
        return $this->server -> send($this->fd,
            match (!is_string($response)) {
                true => json_encode($response, JSON_UNESCAPED_UNICODE),
                default => $response
            }
        );
    }

    public function onClose($server, $fd)
    {}
}