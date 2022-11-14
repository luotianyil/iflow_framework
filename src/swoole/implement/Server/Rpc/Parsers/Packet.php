<?php

namespace iflow\swoole\implement\Server\Rpc\Parsers;

use iflow\swoole\implement\Commounity\Rpc\Request\Routers\CheckRequestRouter;
use iflow\swoole\implement\Server\Rpc\Service;
use Swoole\Server;

class Packet
{

    public function __construct(protected Server $server, protected int $fd, protected array $data) {
    }

    /**
     * 注册
     * @param Service $service
     * @param array $data
     * @return int
     */
    public function register(Service $service, array $data): int {
        return $service -> consumer -> register($data) ? Event::ping -> value : 0;
    }

    /**
     * 客户端关闭连接
     * @param Server $server
     * @param Service $service
     * @param int $fd
     * @return bool
     */
    public function close(Server $server, Service $service, int $fd): bool {
        if ($server -> exist($fd)) $server -> close($fd);
        return $service -> consumer -> remove($fd);
    }

    /**
     * 返回响应
     * @param Service $service
     * @return bool|mixed
     */
    public function send(Service $service): mixed {

        $clientMasterName = $service -> getServicesCommand() -> config -> get('client_name');
        $this->data['client_name'] = $this->data['client_name'] ?? $clientMasterName;

        if ($this->data['client_name'] && $this->data['client_name'] === $clientMasterName) {
            return $this->execute();
        }

        $clientInfo = $service -> consumer -> getByName($this->data['client_name']);

        if (empty($clientInfo) || empty($clientInfo['fd']))
            return $this->server -> send($this->fd, 404);

        $ClientHost = json_decode($clientInfo['host'], true)['tpc'];
        return $this->server->send($this->fd, rpc(
            $ClientHost['host'], $ClientHost['port'],
            $this->data['request_uri'], $this->data['isSSL'] ?? false, [
            'client_name' => $this->data['client_name'],
            'event' => $this->data['event']
        ]) -> getData());
    }

    /**
     * 执行 RPC 路由定义
     * @return bool
     */
    protected function execute(): bool {
        return (new CheckRequestRouter()) -> init($this->server, $this->fd, $this->data);
    }
}