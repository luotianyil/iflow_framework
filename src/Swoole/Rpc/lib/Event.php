<?php


namespace iflow\Swoole\Rpc\lib;

use iflow\Response;
use iflow\Swoole\Rpc\lib\router\rpcRouterBase;
use iflow\Swoole\Services\Http\HttpServer;

class Event extends HttpServer
{

    public mixed $data = [];
    public object $server;
    public int $fd = 0;
    public array $events = [
        'connect' => 'onConnection',
        'receive' => 'onReceive',
        'close' => 'onClose',
        'task' => 'onTask',
        'request' => 'onRequest'
    ];

    public function onReceive($server, $fd, $reactor_id, $data)
    {
        $this->data = json_decode($data, true);
        $this->server = $server;
        $this->fd = $fd;
        if (is_array($this->data)) {
            $this->isTpc = true;
            return $this->rpcValidateRouter();
        }
        return $server -> send($fd, 403);
    }

    public function onOpen($server, $req)
    {
        // TODO: Implement onOpen() method.
    }

    public function onConnection($server, $fd)
    {}

    public function onTask()
    {}

    public function onClose()
    {}

    public function rpcValidateRouter()
    {
        if (empty($this->data['request_uri'])) {
            return $this->send(404);
        }

        $this->router = app() -> make(rpcRouterBase::class) -> validateRouter(
            $this->data['request_uri'],
            $this->data['method'] ?? 'get',
            $this->data
        );

        if (!$this->router) return $this->send(404);
        else {
            return $this->newInstanceController();
        }
    }

    protected function send($response): bool
    {
        if ($this->isTpc) {
            if ($this->fd !== 0) {
                $param[] = $this->fd;
            }

            if ($response instanceof Response) $response = $response -> output($this->data);

            $param[] = match (!is_string($response)) {
                true => json_encode($response, JSON_UNESCAPED_UNICODE),
                default => $response
            };
            return $this->server -> send(...$param);
        }
        return parent::send($response);
    }
}