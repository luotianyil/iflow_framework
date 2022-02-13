<?php


namespace iflow\Swoole\Rpc\lib\router;


use iflow\App;
use iflow\exception\lib\HttpException;
use iflow\exception\lib\HttpResponseException;
use iflow\http\Kernel\Request\RequestInitializer;
use iflow\Response;
use iflow\Swoole\Services;

class checkRequest extends RequestInitializer {

    protected object $server;
    protected int $fd = 0;
    protected mixed $data;

    public function init(object $server, int $fd, mixed $data): bool {
        $this->server = $server;
        $this->fd = $fd;
        $this->data = $data;
        $this->services = app(Services::class);

        try {
            foreach (['QueryRouter', 'RunAop', 'ReturnsResponseBody'] as $action) {
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
    protected function QueryRouter(): bool {

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
        return $this->GenerateControllerService();
    }

    // 验证响应数据
    public function ResponseBodyValidate($response): bool {
        if ($response instanceof Response) return $this->send($response);
        if ($response === false) {
            return $this->send(404);
        }
        return false;
    }

    // 发送信息
    protected function send($response): bool {
        if ($this->fd !== 0) $param[] = $this->fd;
        if ($response instanceof Response) $response = $response -> output($response -> data);

        $param[] = match (!is_string($response)) {
            true => json_encode($response, JSON_UNESCAPED_UNICODE),
            default => $response
        };
        return $this->server -> send(...$param);
    }
}