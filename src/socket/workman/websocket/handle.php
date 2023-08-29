<?php


namespace iflow\socket\workman\websocket;

use iflow\swoole\implement\Server\WebSocket\PacketFormatter\SocketIO\PacketFormatter;
use Workerman\Connection\TcpConnection;
use Workerman\Protocols\Http\Request;
use Workerman\Worker;

class handle
{

    public array $events = [
        'onMessage' => 'message',
        'onClose' => 'close',
        'onWebSocketConnect' => 'connect'
    ];

    protected PacketFormatter $packet;

    protected Ping $ping;

    protected string $EIO;

    protected string $sid;

    protected string $nsp;

    public int $id = 0;
    public TcpConnection $connection;

    public function __construct(protected array $config, protected Worker $worker) {
        $this->packet = new PacketFormatter();
    }

    public function connect(TcpConnection $connection, Request $request): void {
        $this->sid = base64_encode(uniqid());

        $this->EIO = $request -> get('EIO', '');
        $this->nsp = rtrim($request -> path(), '/');

        $this->ping = new Ping($connection, $this->config['ping_interval'], $this->config['ping_timeout']);

        $payload = json_encode(
            [
                'sid'          => $this->sid,
                'upgrades'     => [],
                'pingInterval' => $this->config['ping_interval'],
                'pingTimeout'  => $this->config['ping_timeout']
            ]
        );

        $connection -> send(PacketFormatter::OPEN. $payload);

        if ($this->EIO < 4) {
            $this->ping -> clearPingTimeOut();
        }

        if ($this->EIO >= 4) {
            $packet = PacketFormatter::create(PacketFormatter::CONNECT);
            $packet->data = ['sid' => $this->sid];
            $connection -> send(
                PacketFormatter::message($packet -> toString(), nsp: $this->nsp)
            );
            $this->ping -> ping();
        }
    }

    public function message(TcpConnection $connection, $message): void {
        $data = $this->packet::fromString($message);

        $this -> id = $connection -> id;
        $this->connection = $connection;

        match (intval($data -> type)) {
            PacketFormatter::MESSAGE => $this->Parser($data -> data, $connection),
            // response
            PacketFormatter::PING => $connection -> send(PacketFormatter::PONG),
            PacketFormatter::PONG => $this->ping -> ping(),
            default => $this -> close($connection)
        };
    }

    public function close(TcpConnection $connection): bool {
        $connection -> close();
        return true;
    }

    protected function Parser($data, $connection)
    {
        $data = PacketFormatter::decode($data);
        $event = match (intval($data -> type)) {
            PacketFormatter::OPEN => $this->onConnection($data, $connection),
            PacketFormatter::EVENT => function () use ($data, $connection) {
                // 接受信息回调
                $classes = $this->config['event'] ?? '';
                if (class_exists($classes)) {
                    call_user_func([
                        new $classes, 'handle'
                    ], $this, [
                        'event' => $data->data [0],
                        'data' => isset($data->data[1]) ? (
                        json_decode($data->data[1], JSON_UNESCAPED_UNICODE) ?: $data->data[1]
                        ) : ''
                    ], $data->nsp
                    );
                }
            },
            PacketFormatter::PING => $connection -> send(PacketFormatter::PONG),
            PacketFormatter::PONG => $connection -> send(PacketFormatter::ping()),
            default => false
        };
        return is_callable($event) ? call_user_func($event) : true;
    }

    protected function onConnection(PacketFormatter $data = null, $connection = null): bool {
        $packet = PacketFormatter::create(PacketFormatter::CONNECT);
        if ($this->EIO >= 4) {
            $packet -> data = [
                'sid' => $this->sid
            ];
        }
        $connection -> send(PacketFormatter::message($packet -> toString(), nsp: $data ? $data -> nsp : '/'));
        return true;
    }

    /**
     * @return Worker
     */
    public function getWorker(): Worker {
        return $this->worker;
    }

}