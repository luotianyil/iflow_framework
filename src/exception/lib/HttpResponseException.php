<?php


namespace iflow\exception\lib;


use iflow\Response;
use Psr\Http\Message\ResponseInterface;

class HttpResponseException extends \RuntimeException
{
    protected Response|ResponseInterface $response;

    public function __construct(Response|ResponseInterface $response) {
        $this->response = $response;
    }

    public function getResponse(): Response|ResponseInterface
    {
        return $this->response;
    }
}