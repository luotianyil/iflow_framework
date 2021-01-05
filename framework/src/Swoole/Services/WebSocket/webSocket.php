<?php


namespace iflow\Swoole\Services\WebSocket;

use iflow\Swoole\Services\Services;
use iflow\Swoole\Services\WebSocket\socketio\Event;

class webSocket
{

    protected array $events = [
//        'message' => 'onMessage',
//        'close' => 'onClose',
//        'open' => 'onOpen'
    ];

    public function initializer(Services $services)
    {
//        $services -> eventInit(new Event(), $this->events);

        $services -> getServer() -> on('open', function ($server) {
//            $services -> response ->upgrade();
            var_dump('open');
        });

        $services -> getServer() -> on('message', function () {
            var_dump(123);
        });
    }

    public function emit($fds, $data)
    {

    }
}