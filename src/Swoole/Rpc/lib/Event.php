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
        if ($this->startRpcResponse()) return $this -> send(403);
        return true;
    }

    public function startRpcResponse()
    {
        $this->isTpc = true;
        if (is_array($this->data)) {
            $this->runProcess = array_diff($this->runProcess, ['validateRouter', 'runMiddleware']);
            array_unshift($this->runProcess, 'rpcValidateRouter');
            foreach ($this->runProcess as $key) {
                if (method_exists($this, $key) && call_user_func([$this, $key])) {
                    return $key !== 'startController';
                }
            }
            return false;
        }
        return true;
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

    public function rpcValidateRouter(): bool
    {
        if (empty($this->data['request_uri'])) {
            return $this->send(404);
        }

        $this->router = app() -> make(rpcRouterBase::class) -> validateRouter(
            $this->data['request_uri'],
            $this->data['method'] ?? 'get',
            $this->data
        );

        if (!$this->router) {
            return $this->send(404);
        }
        return $this->newInstanceController();
    }

    protected function send($response): bool
    {
        if ($this->isTpc) {
            if ($this->fd !== 0) {
                $param[] = $this->fd;
            }

            if ($response instanceof Response) $response = $response -> output($response -> data);

            $param[] = match (!is_string($response)) {
                true => json_encode($response, JSON_UNESCAPED_UNICODE),
                default => $response
            };
            return $this->server -> send(...$param);
        }
        return parent::send($response);
    }
}