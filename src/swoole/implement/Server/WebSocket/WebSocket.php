<?php

namespace iflow\swoole\implement\Server\WebSocket;

use iflow\Container\Container;
use iflow\Request;
use iflow\Response;
use iflow\swoole\abstracts\ServicesAbstract;
use iflow\swoole\implement\Server\implement\Room;
use iflow\swoole\implement\Server\WebSocket\Event\Events;
use iflow\swoole\implement\Server\WebSocket\PacketPaser\SocketIO\Emit;
use iflow\swoole\implement\Server\WebSocket\PacketPaser\SocketIO\Packet;
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
        $this->events = Container::getInstance() -> make(Events::class, [
            $this->config, $this
        ]);
    }


    public function createRoom() {
        // 初始化房间信息
        $this->room = Container::getInstance() -> make(Room::class, [
            $this->config['roomType'] ?? 'websocket', $this->servicesAbstract -> getSwService()
        ]);
        $this->RegisterRoomFields();
    }

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

    public function emit(array $data, string $emitClass) {
        // TODO: 返回信息
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

    public function sender(string $event, mixed $data, int $fd = 0): bool {
        $data = Packet::create('4'.Packet::EVENT . $this->nsp. ',', [
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
        $data = Packet::create('4'.Packet::EVENT . $this->nsp. ',', [
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

    /**
     * 添加字段
     * @return void
     */
    protected function RegisterRoomFields() {
        foreach ($this->config['websocket']['fields'] as $field) {
            $this->room -> addField($field);
        }

        $this->room -> getTable() -> create();
    }
}