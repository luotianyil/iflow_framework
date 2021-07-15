<?php


namespace iflow\socket\workman\http;

use iflow\socket\workman\http\lib\Request;
use iflow\socket\workman\http\lib\Response;
use iflow\Swoole\Services\Http\HttpServer;

class handle
{
    public array $events = [
        'onMessage' => 'message'
    ];

    protected HttpServer $httpServer;

    public function __construct()
    {
        $this->httpServer = new HttpServer();
        $this->httpServer -> services = new \iflow\http\lib\service(app());
    }

    public function message($connection, \Workerman\Protocols\Http\Request $request)
    {
        $response = new Response($connection);
        $request = new Request($request);
        $this->httpServer -> onRequest($request, $response);
    }
}