<?php

namespace iflow\swoole\implement\Server\GRpc;

use Google\Protobuf\Internal\Message;

class PackFormatter {

    public static function serialize(Message $message): string {
        return static::pack($message->serializeToString());
    }

    public static function deserialize(Message $message, string $data): void {
        $message -> mergeFromString(static::unpack($data));
    }

    public static function pack(string $data): string {
        return pack('CN', 0, strlen($data)) . $data;
    }

    public static function unpack(string $data): string {
        return substr($data, 5);
    }
}
