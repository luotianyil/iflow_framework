<?php

namespace iflow\http\Kernel\Request;

use iflow\aop\Aop;
use iflow\event\lib\Abstracts\SubjectAbstract;
use iflow\Middleware;
use iflow\Request;
use iflow\Response;
use iflow\Router\CheckRule;
use iflow\Router\implement\Swagger\Swagger;
use iflow\Swoole\Services\WebSocket\socketio\SocketIo;
use Psr\Http\Message\ResponseInterface;
use ReflectionClass;

abstract class RequestVerification extends SubjectAbstract {

    /**
     * APP Services
     * @var object
     */
    public object $services;

    public ?Request $request = null;
    public ?Response $response = null;

    /**
     * 当前请求路由信息
     * @var array
     */
    protected array $router;

    /**
     * 当前请求 控制器@方法
     * @var array
     */
    protected array $RequestController;

    /**
     * 当前请求控制器 反射实现类
     * @var ReflectionClass
     */
    protected ReflectionClass $ReflectionClass;

    /**
     * 当前 请求验证 执行声明周期
     * @var array|string[]
     */
    protected array $RunProcessMethods = [
        'RunMiddleware',
        'RouterBeforeValidate',
        'QueryRouter',
        'RunAop',
        'ReturnsResponseBody'
    ];

    /**
     * 当前请求参数
     * @var array
     */
    protected array $RequestQueryParams = [];

    /**
     * 是否查询为 api 信息
     * @param string $url
     * @return Response|bool
     */
    protected function isRequestApi(string $url = ''): Response|bool {
        $url = trim($url, '/');
        $apiPath = config('app@api_path', false);
        if ($apiPath && $url === $apiPath) {
            return json((new Swagger()) -> buildSwaggerApiJson()) -> send();
        }
        return false;
    }

    /**
     * 是否请求静态资源
     * @param string $url
     * @return bool
     */
    protected function isStaticResources(string $url = ''): bool {
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
    protected function isSocketIo(string $url = ''): bool {
        $url = explode('/', trim($url, '/'));
        if (config('swoole.service@websocket.enable')) {
            if ($url[0] === 'socket.io') {
                $SocketIo = app(SocketIo::class);
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
    protected function RouterBeforeValidate(): bool {
        if ($this->isStaticResources($this->request -> request_uri)) return true;
        if ($this->isRequestApi($this->request -> request_uri)) return true;
        if (swoole_success()) {
            if ($this->isSocketIo($this->request -> request_uri)) return true;
        }
        return false;
    }

    /**
     * 运行中间件
     * @return bool
     */
    protected function RunMiddleware(): bool {
        return $this->ResponseBodyValidate(
            $this->services -> app
                -> make(Middleware::class)
                -> initializer($this->services -> app, $this->request, $this->response)
        );
    }

    /**
     * 查询当前路由是否存在
     * @return bool
     */
    protected function QueryRouter(): bool {
        $this->router = $this->services -> app -> make(CheckRule::class)
        -> setRouterConfigKey('http')
        -> checkRule(
            $this->request -> request_uri,
            $this->request -> request_method,
            $this->request -> params() ?? [],
            $this->request -> getDomain()
        );

        $this->request -> setRouter($this -> router);
        return $this -> GenerateControllerService();
    }

    /**
     * 运行AOP拦截
     * @return bool
     */
    protected function RunAop(): bool {
        $this->RequestQueryParams = $this->GenerateRequestQueryParams($this -> router['parameter']);
        $aop = $this->services -> app -> make(Aop::class) -> process(
            $this->RequestController[0], $this->RequestController[1], ...$this -> RequestQueryParams
        );

        if ($aop === false) return false;

        $res = $aop -> then();
        return !($res === true) && $this->send($res === false ? "" : $res);
    }

    /**
     * 验证响应数据
     * @param mixed $response
     * @return bool
     */
    public function ResponseBodyValidate(mixed $response): bool {
        if ($response instanceof Response || $response instanceof ResponseInterface) {
            return $this->send($response);
        }
        if ($response === false) return $this->response -> notFount();
        return false;
    }

    /**
     * 返回响应信息
     * @param mixed $response
     * @return bool
     */
    protected function send(mixed $response): bool {
        if (!$response) return $this->response -> data($response) -> send();
        switch ($response) {
            case $response instanceof Response:
                return $response->send();
            case $response instanceof ResponseInterface:
                // PSR7
                return $this->response
                    -> headers($response -> getHeaders())
                    -> withStatus($response -> getStatusCode())
                    -> data($response -> getBody() -> __toString())
                    -> send();
            case !is_string($response) && !is_numeric($response):
                // 为非字符串时
                return json($response) -> send();
            default :
                return $this->response -> data($response) -> send();
        }
    }

    /**
     * 实例化控制器
     * @return bool
     */
    abstract protected function GenerateControllerService(): bool;

    /**
     * 格式化请求参数
     * @param array $params
     * @return array
     */
    abstract protected function GenerateRequestQueryParams(array $params = []): array;

    /**
     * 初始化对象类型参数
     * @param array $params
     * @return object
     */
    abstract protected function setInstanceValue(array $params): object;
}