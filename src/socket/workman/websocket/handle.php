<?php


namespace iflow\socket\workman\websocket;

use iflow\Swoole\Services\WebSocket\socketio\Packet;
use Workerman\Connection\TcpConnection;
use Workerman\Worker;

class handle
{

    public array $events = [
        'onMessage' => 'message',
        'onClose' => 'close',
        'onWebSocketConnect' => 'connect'
    ];

    protected Packet $packet;
    protected Ping $ping;

    protected string $EIO;
    protected string $sid;

    public int $id = 0;
    public TcpConnection $connection;

    public function __construct(protected array $config, protected Worker $worker)
    {
        $this->packet = new Packet();
    }

    public function connect(TcpConnection $connection, $http_header)
    {
        $this->sid = base64_encode(uniqid());
        $this->EIO = $_GET['EIO'] ?? '';

        $payload = json_encode(
            [
                'sid'          => $this->sid,
                'upgrades'     => [],
                'pingInterval' => $this->config['ping_interval'],
                'pingTimeout'  => $this->config['ping_timeout']
            ]
        );

        $this->ping = new Ping(
            $connection, $this->config['ping_interval'], $this->config['ping_timeout']
        );
        $connection -> send(
            Packet::OPEN. $payload
        );
        if ($this->EIO < 4) {
            $this->ping -> clearPingTimeOut();
            $this->onConnection(connection: $connection);
        } else {
            $this->ping -> ping();
        }
    }

    public function message(TcpConnection $connection, $message)
    {
        $data = $this->packet::fromString($message);

        $this -> id = $connection -> id;
        $this->connection = $connection;

        match (intval($data -> type)) {
            Packet::MESSAGE => $this->Parser($data -> data, $connection),
            // response
            Packet::PING => $connection -> send(Packet::PONG),
            Packet::PONG => $this->ping -> ping(),
            default => $this -> close($connection)
        };
    }

    public function close(TcpConnection $connection): bool
    {
        $connection -> close();
        return true;
    }

    protected function Parser($data, $connection)
    {
        $data = Packet::decode($data);
        $event = match (intval($data -> type)) {
            Packet::OPEN => $this->onConnection($data, $connection),
            Packet::EVENT => function () use ($data, $connection) {
                // 接受信息回调
                $classes = $this->config['event'] ?? '';
                if (class_exists($classes)) {
                    call_user_func([
                        new $classes, 'handle'
                    ], ...[
                        $this,
                        [
                            'event' => $data -> data [0],
                            'data' => isset($data -> data[1]) ? (
                                json_decode($data -> data[1], JSON_UNESCAPED_UNICODE) ?: $data -> data[1]
                            ) : ''
                        ],
                        $data -> nsp
                    ]);
                }
            },
            Packet::PING => $connection -> send(Packet::PONG),
            Packet::PONG => $connection -> send(packet::ping()),
            default => false
        };
        return is_callable($event) ? call_user_func($event) : true;
    }

    protected function onConnection(Packet $data = null, $connection = null): bool
    {
        $packet = Packet::create(Packet::CONNECT);
        if ($this->EIO >= 4) {
            $packet -> data = [
                'sid' => $this->sid
            ];
        }
        $connection -> send(Packet::message($packet -> toString(), nsp: $data ? $data -> nsp : '/'));
        return true;
    }

    /**
     * @return Worker
     */
    public function getWorker(): Worker
    {
        return $this->worker;
    }

}