<?php


namespace iflow\Swoole\MQTT\lib;


use BinSoul\Net\Mqtt\PacketStream;

class Parser
{

    protected PacketStream $packetStream;

    public function decoding($data = ''): PacketStream
    {
        return new PacketStream($data);
    }
}