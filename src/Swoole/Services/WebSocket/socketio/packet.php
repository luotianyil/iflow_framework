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

    public string $nsp = '/';
    public int|string $id;

    public function __construct(
        public string $type = '',
        public string|array $data = ''
    ){}

    public function open($data)
    {
        return (new static(self::OPEN, $data)) -> toString();
    }

    public function pong($data)
    {
        return (new static(self::PONG, $data)) -> toString();
    }

    public static function ping()
    {
        return self::PING;
    }

    public static function message($payload, int $offset = 1, string $nsp = '/')
    {
        $type = substr($payload, 0, $offset);
        $payload = substr($payload, $offset);
        return (new static(self::MESSAGE.$type . "$nsp,", $payload)) -> toString();
    }

    public static function fromString(string $packet)
    {
        return new static(substr($packet, 0, 1), substr($packet, 1) ?? '');
    }

    public static function create($type, array $decoded = [])
    {
        $new     = new static($type);
        $new->id = $decoded['id'] ?? '';
        if (isset($decoded['nsp'])) {
            $new->nsp = $decoded['nsp'] ?: '/';
        } else {
            $new->nsp = '/';
        }
        $new->data = $decoded['data'] ?? '';
        return $new;
    }

    public function toString(): string
    {
        if (is_array($this->data)) {
            $this->data = json_encode($this->data);
        }
        return $this->type . $this->data;
    }
}