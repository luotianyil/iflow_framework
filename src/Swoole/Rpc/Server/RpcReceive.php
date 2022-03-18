<?php

namespace iflow\Swoole\Rpc\Server;

use iflow\facade\Cache;
use iflow\Swoole\Rpc\Router\CheckRequestRouter;
use Swoole\Server;

class RpcReceive {

    public array $router;
    public int $fd;
    protected Server $server;
    protected ?array $clientList = [];
    protected mixed $config = [];

    public function handle(Server $server, int $fd, $reactor_id, $data): bool {
        $this->server = $server;
        $this->fd = $fd;

        // 返回 PONG
        if (intval($data) === 1) return $this->send(2);

        // 获取当前服务配置
        $this->config = config('swoole.rpc@server');
        $info = json_decode($data, true) ?: [];

        // 获取当前 服务在线 列表
        $this->clientList = Cache::store($this->config['clientList'])
            -> get($this->config['clientList']['cacheName']);

        // 验证请求数据
        if ($info && is_array($info)) {
            if (isset($info['isClientConnection']) && isset($info['client_name'])) {
                // 验证是否请求主服务器
                if ($info['client_name'] === $this->config['client_name']) {
                    return (new CheckRequestRouter()) -> init($server, $fd, $info);
                }
                // 向指定子级服务发送请求
                return $this -> send(rpc($info['client_name'], $info['request_uri'], $info));
            }

            // 验证是否为新增子服务器
            if ($this->appendClient($info, $fd)) {
                return $this -> send($info);
            }
        }

        return $this->send(404);
    }


    /**
     * 将当前 子级服务加入 列表
     * @param array $info
     * @param int $fd
     * @return bool
     * @throws \Exception
     */
    public function appendClient(array $info, int $fd): bool {
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

    /**
     * 返回请求响应
     * @param $response
     * @return bool
     */
    protected function send($response): bool {
        return $this->server -> send($this->fd,
            match (!is_string($response) || !is_numeric($response)) {
                true => json_encode($response, JSON_UNESCAPED_UNICODE),
                default => $response
            }
        );
    }

    public function onClose($server, $fd) {}
}