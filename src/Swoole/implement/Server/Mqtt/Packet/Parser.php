<?php

namespace iflow\swoole\implement\Server\Mqtt\Packet;

use Simps\MQTT\Protocol\V3;
use Simps\MQTT\Protocol\V5;

class Parser {

    public function unpack($data, $protocol_level = 5): mixed
    {
        $data = trim(trim($data, '"'), '');
        if (strlen($data) > 0 && $data !== '') {
            return $this -> Protocol($protocol_level === 5 ? V5::class : V3::class , $data);
        }
        return [];
    }

    public function pack(array $data, $protocol_level = 5): mixed
    {
        return $this -> Protocol($protocol_level === 5 ? V5::class : V3::class , $data, 'pack');
    }

    protected function Protocol($class, $data, $func = 'unpack'): mixed
    {
        try {
            if (class_exists($class)) return $class::$func($data);
        } catch (\Exception $exception) {
            logs('error', $exception -> getMessage()) -> update();
        }
        return [];
    }

}