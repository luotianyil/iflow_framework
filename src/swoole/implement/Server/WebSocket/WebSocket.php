<?php

namespace iflow\swoole\implement\Server\WebSocket;

use iflow\Container\Container;
use iflow\Container\implement\generate\exceptions\InvokeClassException;
use iflow\Request;
use iflow\Response;
use iflow\swoole\abstracts\ServicesAbstract;
use iflow\swoole\implement\Server\implement\Room\Room;
use iflow\swoole\implement\Server\WebSocket\Event\Events;
use iflow\swoole\implement\Server\WebSocket\PacketFormatter\SocketIO\Emit;
use iflow\swoole\implement\Server\WebSocket\PacketFormatter\SocketIO\PacketFormatter;
use Swoole\Server;

class WebSocket {

    protected array $transports = [ 'polling', 'websocket' ];

    protected Server $server;

    protected Events $events;

    public int $fd = 0;
    public string $nsp;

    protected array $eventsKey = [
        'message' => 'onMessage',
        'close' => 'onClose',
        'open' => 'onOpen'
    ];

    public array $to = [];

    public Room $room;

    public function __construct(protected array $config, protected ServicesAbstract $servicesAbstract) {
        $this->events = app(Events::class, [
            $this->config, $this
        ]);
    }


    /**
     * 初始化房间信息
     * @return void
     * @throws InvokeClassException
     */
    public function createRoom(): void {
        if (isset($this->config['websocket']['room'])) {
            $this->room = app(Room::class, [
                $this->config['websocket']['room']['roomType'] ?? 'websocket',
                $this,
                $this->config['websocket']['room']
            ]);
        }
    }

    /**
     * 创建链接
     * @param Request $request
     * @param Response $response
     * @return string|Response
     */
    public function connection(Request $request, Response $response): string|Response {
        if (!in_array($request->getParams('transport'), $this->transports)) {
            return json([
                'code' => 0,
                'msg' => 'Transport unknown'
            ], 400);
        }
        if ($request -> params('sid') !== null) return '1:16';
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

    /**
     * 发送信息
     * @param array $data
     * @param string $emitClass
     * @return bool|void
     * @throws InvokeClassException
     */
    public function emit(array $data, string $emitClass) {

        if (class_exists($emitClass)) {
            $emit = Container::getInstance() -> make($emitClass, [
                $this, $data
            ]);

            if ($emit instanceof Emit) {
                return $emit -> handle();
            }
        }
        return $this->sender('E', [ 'Non-Data' ]);
    }

    /**
     * @param string $event
     * @param mixed $data
     * @param int $fd
     * @return bool
     */
    public function sender(string $event, mixed $data, int $fd = 0): bool {
        $data = PacketFormatter::create('4'.PacketFormatter::EVENT . $this->nsp. ',', [
            'data' => [ $event, $data ]
        ]) -> toString();
        try {
            if (empty($this->to) && $fd === 0) $this->to[] = $this->fd;
            elseif ($fd > 0) $this->to[] = $fd;
            foreach ($this->to as $fd) {
                if (!$this->server -> exist($fd)) continue;
                $this->server -> push($fd, $data);
            }
            return true;
        } finally {
            $this->to = [];
            return false;
        }
    }

    /**
     * 指定fd 发送信息
     * @param int $fd
     * @param mixed $data
     * @return bool
     */
    public function send (int $fd, mixed $data): bool {
        $data = PacketFormatter::create('4'.PacketFormatter::EVENT . $this->nsp. ',', [
            'data' => [ $data['event'], $data['body'] ]
        ]) -> toString();
        return $this -> server -> send($fd, $data);
    }

    /**
     * @return array
     */
    public function getEventsKey(): array {
        $events = [];
        foreach ($this->eventsKey as $eventName => $action) {
            $events[$eventName] = [ $this->events, $action ];
        }
        return $events;
    }
}