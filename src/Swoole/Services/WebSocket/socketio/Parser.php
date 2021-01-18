<?php


namespace iflow\Swoole\Services\WebSocket\socketio;


class Parser extends packet
{

    public static function heartbeat($server, $fd, $packet)
    {
        $packetLength = strlen($packet);
        $payload      = '';

        if ($isPing = self::isSocketType($packet, 'ping')) {
            $payload .= self::PONG;
        }

        if ($isPing && $packetLength > 1) {
            $payload .= substr($packet, 1, $packetLength - 1);
        }

        if ($isPing) {
            $server->push($fd, $payload);
        }
    }

    public static function getSocketType(string $packet)
    {
        $type = $packet[0] ?? null;
        if (!array_key_exists($type, static::$socketTypes)) {
            return false;
        }
        return (int) $type;
    }

    public static function isSocketType($packet, string $typeName)
    {
        $type = array_search(strtoupper($typeName), static::$socketTypes);
        return $type === false ? false : static::getSocketType($packet) === $type;
    }

    public static function getPayload(string $data)
    {
        $len = strlen($data);
        $start = strpos($data, '[');

        if ($start === false || substr($data, -1) !== ']') {
            return false;
        }

        $data = substr($data, $start, $len - $start);
        $data = self::decode($data);

        if (is_null($data)) {
            return false;
        }

        return [
            'event' => $data[0],
            'data'  => $data[1] ?? null,
        ];
    }

    public static function encode($event, $data)
    {
        return json_encode([
            'event' => $event,
            'data' => $data
        ]);
    }

    public static function decode($data)
    {
        $data = json_decode($data, true);
        return [
            'event' => $data['event'] ?? null,
            'data'  => $data['data'] ?? null,
        ];
    }
}