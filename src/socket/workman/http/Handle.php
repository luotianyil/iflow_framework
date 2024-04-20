<?php


namespace iflow\socket\workman\http;

use iflow\socket\workman\http\implement\Request;
use iflow\socket\workman\http\implement\Response;
use Workerman\Connection\TcpConnection;

class Handle
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