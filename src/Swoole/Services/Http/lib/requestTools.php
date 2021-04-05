<?php


namespace iflow\Swoole\Services\Http\lib;


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
        'startController'
    ];

    protected \ReflectionClass $refController;

    protected function isRequestApi(string $url = ''): Response|bool
    {
        $url = trim($url, '/');
        if ($url === config('app@api_path')) {
            message() -> success('success', config('router')) -> send();
            return true;
        }
        return false;
    }

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

    protected function validateRouterBefore(): bool {
        if ($this->isStaticResources($this->request -> request_uri)) return true;
        if ($this->isRequestApi($this->request -> request_uri)) return true;
        if (swoole_success()) {
            if ($this->isSocketIo($this->request -> request_uri)) return true;
        }
        return false;
    }

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

    protected function runMiddleware(): bool {
        $middleware = $this->services -> app
            -> make(Middleware::class)
            -> initializer($this->services -> app, $this->request, $this->response);
        if ($this->validateResponse($middleware)) {
            return true;
        }
        return false;
    }

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