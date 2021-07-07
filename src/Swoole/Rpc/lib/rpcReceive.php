<?php


namespace iflow\Swoole\Rpc\lib;

use iflow\facade\Cache;
use iflow\Swoole\Rpc\lib\router\checkRequest;
use Swoole\Server;

class rpcReceive
{

    public array $router;
    public int $fd;
    protected Server $server;
    protected ?array $clientList = [];
    protected mixed $config = [];

    public function handle(Server $server, $fd, $reactor_id, $data): bool
    {
        $this->server = $server;
        $this->fd = $fd;
        // 返回 PONG
        if (intval($data) === 1) return $this->send(2);

        $this->config = config('swoole.rpc@server');
        $info = json_decode($data, true);

        $this->clientList = Cache::store($this->config['clientList'])
                            -> get($this->config['clientList']['cacheName']);
        if ($info) {
            // 向子服务器发送请求
            if (isset($info['isClientConnection']) && isset($info['client_name'])) {
                // 验证是否请求自己
                if ($info['client_name'] === $this->config['client_name']) {
                    return (new checkRequest())
                        -> init($server, $fd, $info);
                }

                return $this -> send(rpc($info['client_name'], $info['request_uri'], $info));
            }
            if ($this->addClient($info, $fd)) {
                return $this -> send($info);
            }
        }
        return $this->send(404);
    }

    public function addClient($info, $fd): bool
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
            match (!is_string($response) || !is_numeric($response)) {
                true => json_encode($response, JSON_UNESCAPED_UNICODE),
                default => $response
            }
        );
    }

    public function onClose($server, $fd)
    {}
}