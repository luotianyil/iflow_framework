<?php


namespace iflow\socket\workman\http\implement;

class Response
{
    protected \Workerman\Protocols\Http\Response $response;

    public function __construct(
        protected $connection
    ) {
        $this->response = new \Workerman\Protocols\Http\Response();
    }

    public function status(int $status): Response {
        $this -> response -> withStatus($status);
        return $this;
    }

    public function end(string $data = ''): bool
    {
        $this->response -> withBody($data);
        $this -> connection -> send($this->response);
        return true;
    }

    public function sendfile($path): bool
    {
        $this->response -> withFile($path);
        $this -> connection -> send($this->response);
        return true;
    }

    public function __call(string $name, array $arguments)
    {
        // TODO: Implement __call() method.
        if (!method_exists($this->response, $name)) return null;
        return call_user_func([$this->response, $name], ...$arguments);
    }
}