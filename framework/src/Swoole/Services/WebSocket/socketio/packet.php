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

}