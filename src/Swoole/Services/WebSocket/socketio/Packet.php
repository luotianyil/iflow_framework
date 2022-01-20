<?php


namespace iflow\Swoole\Services\WebSocket\socketio;


class Packet
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

    public function open($data): string {
        return (new static(self::OPEN, $data)) -> toString();
    }

    public function pong($data): string {
        return (new static(self::PONG, $data)) -> toString();
    }

    public static function ping(): int {
        return self::PING;
    }

    public static function message($payload, int $offset = 1, string $nsp = '/'): string {
        $type = substr($payload, 0, $offset);
        $payload = substr($payload, $offset);
        return (new static(self::MESSAGE.$type . "$nsp,", $payload)) -> toString();
    }

    public static function fromString(string $packet) {
        return new static(substr($packet, 0, 1), substr($packet, 1) ?? '');
    }

    public static function create($type, array $decoded = []) {
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

    public static function decode(string $str) {
        $i = 0;
        $packet = new Packet((int) substr($str, 0, 1));
        if ('/' === substr($str, $i + 1, 1)) {
            $packet->nsp = explode(',', substr($str, 1))[0];
        } else {
            $packet->nsp = '/';
        }
        $i = strlen($packet -> nsp) + 1;

        $next = substr($str, $i + 1, 1);

        if ('' !== $next && is_numeric($next)) {
            $id = '';
            while (++$i) {
                $c = substr($str, $i, 1);
                if (null == $c || !is_numeric($c)) {
                    --$i;
                    break;
                }
                $id .= substr($str, $i, 1);
                if ($i === strlen($str)) {
                    break;
                }
            }
            $packet->id = intval($id);
        }

        if (substr($str, ++$i, 1)) {
            $packet->data = json_decode(substr($str, $i), true) ?: substr($str, $i);
        }

        return $packet;
    }

    public function toString(): string {
        if (is_array($this->data)) {
            $this->data = json_encode($this->data);
        }
        return $this->type . $this->data;
    }
}