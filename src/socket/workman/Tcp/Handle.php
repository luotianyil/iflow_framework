<?php

namespace iflow\socket\workman\Tcp;

use iflow\swoole\implement\Server\WebSocket\PacketFormatter\SocketIO\PacketFormatter;
use Workerman\Connection\TcpConnection;
use Workerman\Worker;

class Handle {


    public array $events = [
        'onMessage' => 'message',
        'onClose' => 'close',
        'onConnect' => 'connect'
    ];

    public function __construct(protected array $config, protected Worker $worker) {
        $this->packet = new PacketFormatter();
    }

    public function message(TcpConnection $connection, mixed $data): void {
        $this -> trigger('onMessage', $connection, $data);
    }


    public function connect(TcpConnection $connection): void {
        $this -> trigger('onConnect', $connection);
    }

    public function close(TcpConnection $connection): void {
        $this -> trigger('onClose', $connection);
    }


    protected function trigger(string $event, ...$args): void {
        if (empty($this->config['event']) || !class_exists($this->config['event'])) return;
        app() -> invokeClass($this->config['event'], [ $this -> worker ]) -> {$event}(...$args);
    }

}