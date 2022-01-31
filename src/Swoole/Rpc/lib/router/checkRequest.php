<?php


namespace iflow\Swoole\Rpc\lib\router;


use iflow\exception\lib\HttpException;
use iflow\exception\lib\HttpResponseException;
use iflow\Response;
use iflow\Swoole\Services\Http\lib\initializer;

class checkRequest extends initializer
{

    protected object $server;
    protected int $fd = 0;
    protected mixed $data;

    public function init(object $server, int $fd, mixed $data): bool {
        $this->server = $server;
        $this->fd = $fd;
        $this->data = $data;

        try {
            foreach (['startValidRequest', 'runAop', 'startController'] as $action) {
                if (method_exists($this, $action) && call_user_func([$this, $action])) break;
            }
            return true;
        } catch (HttpResponseException|HttpException $exception) {
            if ($exception instanceof HttpResponseException) {
                return $this->send($exception -> getResponse());
            }
            return $this->send($exception -> getMessage());
        }
    }

    /**
     * @throws \ReflectionException
     */
    protected function startValidRequest(): bool {

        if (empty($this->data['request_uri'])) {
            return $this->send(404);
        }
        $this->router = app() -> make(rpcRouterBase::class) -> checkRule(
            $this->data['request_uri'],
            $this->data['method'] ?? 'get',
            $this->data
        );

        if (!$this->router) {
            return $this->send(404);
        }
        return $this->newInstanceController();
    }

    // 验证响应数据
    public function validateResponse($res): bool
    {
        if ($res instanceof Response) {
            return $this->send($res);
        }

        if ($res === false) {
            return $this->send(404);
        }
        return false;
    }

    // 发送信息
    protected function send($response): bool
    {
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
}