<?php


namespace iflow\Swoole\Services\Http\lib;


use iflow\annotation\lib\value\Exception\valueException;
use iflow\aop\Aop;
use iflow\Middleware;
use iflow\Request;
use iflow\Response;
use iflow\Swoole\Services\WebSocket\socketio\SocketIo;

class requestTools
{

    public object $services;
    public Request $request;
    public Response $response;

    public bool $isTpc = false;
    public array $router;

    public array $requestController = [];

    protected array $runProcess = [
        'runMiddleware',
        'validateRouterBefore',
        'validateRouter',
        'runAnnotation',
        'runAop',
        'startController'
    ];

    protected \ReflectionClass $refController;

    /**
     * 是否查询为 api 信息
     * @param string $url
     * @return Response|bool
     */
    protected function isRequestApi(string $url = ''): Response|bool
    {
        $url = trim($url, '/');
        $apiPath = config('app@api_path') ?: false;
        if ($apiPath && $url === $apiPath) {
            message() -> success('success', config(config('app@router')['key'])) -> send();
            return true;
        }
        return false;
    }

    /**
     * 是否请求静态资源
     * @param string $url
     * @return bool
     */
    protected function isStaticResources(string $url = ''): bool
    {
        $url = explode('/', trim($url, '/'));
        $rule = config('app@resources.file');
        if ($url[0] === $rule['rule']) {
            array_splice($url, 0, 1);
            $url = str_replace('/', DIRECTORY_SEPARATOR, implode('/', $url));
            sendFile($rule['rootPath'] . DIRECTORY_SEPARATOR . $url, isConfigRootPath: false) -> send();
            return true;
        }
        return false;
    }

    /**
     * 是否为socket.io
     * @param string $url
     * @return bool
     */
    protected function isSocketIo($url = ''): bool
    {
        $url = explode('/', trim($url, '/'));
        if (config('service@websocket.enable')) {
            if ($url[0] === 'socket.io') {
                $SocketIo = new SocketIo();
                $SocketIo -> config = $this->services -> configs['websocket'];
                return $this->send($SocketIo-> __initializer($this->request, $this->response));
            }
        }
        return false;
    }

    /**
     * 前置路由验证
     * @return bool
     */
    protected function validateRouterBefore(): bool {
        if ($this->isStaticResources($this->request -> request_uri)) return true;
        if ($this->isRequestApi($this->request -> request_uri)) return true;
        if (swoole_success()) {
            if ($this->isSocketIo($this->request -> request_uri)) return true;
        }
        return false;
    }

    /**
     * 执行方法注解
     * @return Response|bool
     * @throws \ReflectionException
     */
    protected function runAnnotation(): Response | bool
    {
        $annotation = $this->refController -> getMethod($this->requestController[1]) -> getAttributes();
        foreach ($annotation as $key) {
            $obj = $key -> newInstance();
            if (method_exists($obj, 'handle') && $this->validateResponse(call_user_func([$obj, 'handle'], $this))) {
                return true;
            }
        }
        return false;
    }

    /**
     * 运行中间件
     * @return bool
     */
    protected function runMiddleware(): bool {
        $middleware = $this->services -> app
            -> make(Middleware::class)
            -> initializer($this->services -> app, $this->request, $this->response);
        if ($this->validateResponse($middleware)) {
            return true;
        }
        return false;
    }

    /**
     * 运行AOP拦截
     * @return bool
     */
    protected function runAop(): bool
    {
        $aop = $this->services -> app -> make(Aop::class) -> process(
            $this->requestController[0], $this->requestController[1], ...$this->bindParam(
                $this->router['parameter']
            )
        );
        if ($aop === false) return false;
        $res = $aop -> then();
        return $res === true ? false : $this->send($res === false ? "" : $res);
    }

    // 绑定参数
    protected function bindParam(array $params = []): array
    {
        $parameter = [];
        foreach ($params as $key => $value) {
            if (isset($value['default'])) {
                $parameter[] = $value['default'];
            } else {
                $parameter[] = $this->setInstanceValue($value);
            }
        }
        return $parameter;
    }

    /**
     * 验证响应数据
     * @param $res
     * @return bool
     */
    public function validateResponse($res): bool
    {
        if ($res instanceof Response) {
            return $res -> send();
        }

        if ($res === false) {
            return $this->response -> notFount();
        }
        return false;
    }

    /**
     * 返回响应信息
     * @param $response
     * @return bool
     */
    protected function send($response): bool
    {
        if ($response instanceof Response) {
            $response -> send();
        } else if (!is_string($response)) {
            json($response) -> send();
        } else if ($this->response) {
            $this->response -> data($response) -> send();
        }
        return true;
    }
}