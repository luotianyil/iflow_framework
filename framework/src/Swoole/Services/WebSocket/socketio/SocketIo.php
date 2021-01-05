<?php


namespace iflow\Swoole\Services\WebSocket\socketio;


use Swoole\Http\Request;

class SocketIo
{

    protected array $transports = ['polling', 'websocket'];

    public array $config = [];

    public function heartbeat()
    {}

    public function __initializer(Request $request, $response): string
    {

        if (!in_array($request->get['transport'], $this->transports)) {
            return json(
                [
                    'code' => 0,
                    'message' => 'Transport unknown',
                ],
                400
            );
        }


        if (!empty($request -> get['sid'])) {
            return '1:16';
        } else {
            $sid     = base64_encode(uniqid());
            $payload = json_encode(
                [
                    'sid'          => $sid,
                    'upgrades'     => ['websocket'],
                    'pingInterval' => $this -> config['ping_interval'],
                    'pingTimeout'  => $this -> config['ping_timeout'],
                ]
            );
            $response -> cookie('io', $sid);
            return '97:0' . $payload . '2:40';
        }
    }

}