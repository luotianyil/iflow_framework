<?php


namespace iflow\Swoole\Services\Http\lib;


use iflow\Middleware;
use iflow\Request;
use iflow\Response;
use iflow\Swoole\Services\Services;

class initializer
{

    public Services $services;
    public Request $request;
    public Response $response;

    public function __initializer(Services $services, $request, $response)
    {
        $this->services = $services;
        $this->setRequest($request)
            -> setResponse($response);
        $services -> app -> make(Middleware::class) -> initializer($services -> app, $this->request, $this->response) -> send();
    }

    public function setRequest($request): static
    {
        $this->request = $this -> services -> app -> make(Request::class) -> initializer($request);
        return $this;
    }

    public function setResponse($response): static
    {
        $this->response = $this -> services -> app -> make(Response::class) -> initializer($response);
        return $this;
    }

}