<?php


namespace iflow\socket\workman\http;

use iflow\socket\workman\http\lib\Request;
use iflow\socket\workman\http\lib\Response;
use Workerman\Connection\TcpConnection;

class handle
{
    public array $events = [
        'onMessage' => 'message'
    ];

    public function __construct(protected array $config, protected $server) {
    }

    public function message(TcpConnection $connection, \Workerman\Protocols\Http\Request $request) {
        $response = new Response($connection);
        $request = new Request($request);
        return event('RequestVerification', $request, $response, microtime(true));
    }
}