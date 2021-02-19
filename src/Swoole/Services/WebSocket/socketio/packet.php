<?php


namespace iflow\Swoole\Services\WebSocket\socketio;


class packet
{
    const OPEN = 0;

    const CLOSE = 1;

    const PING = 2;

    const PONG = 3;

    const MESSAGE = 4;

    const UPGRADE = 5;

    const NOOP = 6;

    const CONNECT = 0;

    const DISCONNECT = 1;

    const EVENT = 2;

    const ACK = 3;

    const ERROR = 4;

    const BINARY_EVENT = 5;

    const BINARY_ACK = 6;

    public static array $socketTypes = [
        'OPEN',
        'CLOSE',
        'PING',
        'PONG',
        'MESSAGE',
        'UPGRADE',
        'NOOP',
    ];

    public static array $engineTypes = [
        'CONNECT',
        'DISCONNECT',
        'EVENT',
        'ACK',
        'ERROR',
        'BINARY_EVENT',
        'BINARY_ACK',
    ];

    public function __construct(
        protected string $type = '',
        protected string $data = ''
    )
    {}

    public function open($data)
    {
        return new static(self::OPEN, $data);
    }

    public function pong($data)
    {
        return new static(self::PONG, $data);
    }

    public static function ping()
    {
        return new static(self::PING);
    }

    public static function message($payload)
    {
        return new static(self::MESSAGE, $payload);
    }

    public static function fromString(string $packet)
    {
        return new static(substr($packet, 0, 1), substr($packet, 1) ?? '');
    }

    public function toString()
    {
        return $this->type . $this->data;
    }
}