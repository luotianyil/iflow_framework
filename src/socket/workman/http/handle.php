<?php


namespace iflow\socket\workman\http;

use iflow\http\lib\Service;
use iflow\socket\workman\http\lib\Request;
use iflow\socket\workman\http\lib\Response;
use iflow\Swoole\Services\Http\HttpServer;
use Workerman\Connection\TcpConnection;

class handle
{
    public array $events = [
        'onMessage' => 'message'
    ];

    protected HttpServer $httpServer;

    public function __construct() {
        $this->httpServer = new HttpServer();
        $this->httpServer -> services = new Service(app());
    }

    public function message(TcpConnection $connection, \Workerman\Protocols\Http\Request $request) {
        $response = new Response($connection);
        $request = new Request($request);
        $this->httpServer -> onRequest($request, $response);
    }
}