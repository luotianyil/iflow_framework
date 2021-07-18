<?php


namespace iflow\Swoole\Services\Http\lib;


use iflow\Swoole\Services\Http\HttpServer;
use Swoole\Http\Request;
use Swoole\Http\Response;

class HttpTask
{

    protected Request|\Swoole\Http2\Request $request;
    protected Response|\Swoole\Http2\Response $response;
    protected HttpServer $httpServer;

    /**
     * @param HttpServer $httpServer
     * @return HttpTask
     */
    public function setHttpServer(HttpServer $httpServer): static
    {
        $this->httpServer = $httpServer;
        return $this;
    }

    /**
     * @param \Swoole\Http2\Request|Request $request
     * @return HttpTask
     */
    public function setRequest(\Swoole\Http2\Request|Request $request): static
    {
        $this->request = $request;
        return $this;
    }

    /**
     * @param \Swoole\Http2\Response|Response $response
     * @return HttpTask
     */
    public function setResponse(\Swoole\Http2\Response|Response $response): static
    {
        $this->response = $response;
        return $this;
    }

    public function onRequest()
    {
        return $this->httpServer -> onRequest($this->request, $this->response);
    }

}