<?php


namespace iflow\Swoole\Services\WebSocket\socketio;

use iflow\Request;
use iflow\Response;

class SocketIo
{

    protected array $transports = ['polling', 'websocket'];

    public array $config = [];

    public function __initializer(Request $request, Response $response): Response|string
    {

        if ($request -> isPost()) {
            return json([
                    'code'    => 3,
                    'message' => 'Bad request',
            ], 400);
        }

        if (!in_array($request->getParams('transport'), $this->transports)) {
            return json([
                'code' => 0,
                'message' => 'Transport unknown',
            ], 400);
        }
        if (!empty($request -> getParams('sid'))) {
            return '1:16';
        } else {
            $sid     = base64_encode(uniqid());
            $payload = json_encode(
                [
                    'sid'          => $sid,
                    'upgrades'     => ['websocket'],
                    'pingInterval' => $this -> config['ping_interval'],
                    'pingTimeout'  => $this -> config['ping_timeout'],
                ], JSON_UNESCAPED_UNICODE);
            $response -> response -> cookie('io', $sid);
            return '97:0' . $payload . '2:40';
        }
    }

}