<?php


namespace iflow\Swoole\MQTT\lib;

use Simps\MQTT\Protocol;
use Simps\MQTT\ProtocolV5;

class Parser
{

    public function unpack($data, $protocol_level = 5)
    {
        $data = trim(trim($data, '"'), '');
        if (is_string($data) && strlen($data) > 0 && $data !== '') {
            return $this -> Protocol($protocol_level === 5 ? ProtocolV5::class : Protocol::class , $data);
        }
        return [];
    }

    protected function Protocol($class, $data)
    {
        try {
            if (class_exists($class)) $class::unpack($data);
        } catch (\Exception $exception) {
            var_dump($exception -> getMessage(), $data);
        }
        return [];
    }

    public function getHeader($data)
    {
        $byte = ord($data[0]);
        $header['type'] = ($byte & 0xF0) >> 4;
        $header['dup'] = ($byte & 0x08) >> 3;
        $header['qos'] = ($byte & 0x06) >> 1;
        $header['retain'] = $byte & 0x01;
        return $header;
    }

    public function decodeString($data)
    {
        $length = $this -> decodeValue($data);
        return substr($data, 2, $length);
    }

    public function decodeValue($data)
    {
        return 256 * ord($data[0]) + ord($data[1]);
    }

    public function eventConnect($header, $data)
    {
        $connect_info['protocol_name'] = $this -> decodeString($data);
        $offset = strlen($connect_info['protocol_name']) + 2;

        $connect_info['version'] = ord(substr($data, $offset, 1));
        $offset += 1;

        $byte = ord($data[$offset]);
        $connect_info['willRetain'] = ($byte & 0x20 == 0x20);
        $connect_info['willQos'] = ($byte & 0x18 >> 3);
        $connect_info['willFlag'] = ($byte & 0x04 == 0x04);
        $connect_info['cleanStart'] = ($byte & 0x02 == 0x02);
        $offset += 1;

        $connect_info['keepalive'] = $this -> decodeValue(substr($data, $offset, 2));
        $offset += 2;
        $connect_info['clientId'] = $this -> decodeString(substr($data, $offset));
        return $connect_info;
    }

}