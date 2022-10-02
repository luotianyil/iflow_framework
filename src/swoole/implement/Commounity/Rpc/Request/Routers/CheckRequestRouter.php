<?php

namespace iflow\swoole\implement\Commounity\Rpc\Request\Routers;

use iflow\exception\Adapter\HttpException;
use iflow\exception\Adapter\HttpResponseException;
use iflow\http\Kernel\Exception\RequestValidateException;
use iflow\http\Kernel\Request\RequestInitializer;
use iflow\Response;
use ReflectionException;

class CheckRequestRouter extends RequestInitializer {

    protected object $server;

    /**
     * 客户端 Id
     * @var int
     */
    protected int $fd;

    /**
     * 请求信息
     * @var mixed
     */
    protected mixed $data;

    /**
     * 初始化验证请求
     * @param object $server
     * @param int $fd
     * @param mixed $data
     * @return bool
     */
    public function init(object $server, int $fd, mixed $data): bool {
        $this->server = $server;
        $this->fd = $fd;
        $this->data = $data;

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
     * 查询路由
     * @throws ReflectionException|RequestValidateException
     */
    protected function QueryRouter(): bool {

        if (empty($this->data['request_uri'])) {
            return $this->send(404);
        }
        $this->router = app() -> make(RpcRouters::class) -> checkRule(
            $this->data['request_uri'],
            $this->data['method'] ?? 'get',
            $this->data
        );

        if (!$this->router) {
            return $this->send(404);
        }
        return $this->GenerateControllerService();
    }

    /**
     * 验证响应数据
     * @param $response
     * @return bool
     */
    public function ResponseBodyValidate($response): bool {
        if ($response instanceof Response) return $this->send($response);
        if ($response === false) {
            return $this->send(404);
        }
        return false;
    }

    /**
     * 返回响应信息
     * @param $response
     * @return bool
     */
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