<?php


namespace iflow\exception\lib;


use iflow\Response;

class HttpResponseException extends \RuntimeException
{
    protected Response $response;

    public function __construct(Response $response)
    {
        $this->response = $response;
    }

    public function getResponse(): Response
    {
        return $this->response;
    }
}