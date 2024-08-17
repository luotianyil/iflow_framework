<?php


namespace iflow\socket\workman\http\implement;

use Workerman\Connection\TcpConnection;
use Workerman\Protocols\Http\ServerSentEvents;
use Workerman\Protocols\Http\Response as WorkerResponse;

class Response {

    protected WorkerResponse $response;

    protected bool $sendServerSendEventHeader = false;

    public function __construct(protected TcpConnection $connection) {
        $this->response = new WorkerResponse(body: "\r\n");
    }

    public function status(int $status): Response {
        $this -> response -> withStatus($status);
        return $this;
    }

    public function serverSentEvents(array $data = [], string $contentType = 'text/event-stream'): bool {
        if (!$this->sendServerSendEventHeader) {
            $this->sendServerSendEventHeader = true;
            $this->connection -> send(
                $this->response -> withHeader('Content-Type', $contentType)
            );
        }

        $this->connection -> send(new ServerSentEvents($data));
        return true;
    }

    public function end(string $data = ''): bool {
        $this -> response -> withBody($data);
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