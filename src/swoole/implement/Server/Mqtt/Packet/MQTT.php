<?php

namespace iflow\swoole\implement\Server\Mqtt\Packet;

class MQTT {

    protected array $data = [];

    public function __construct( protected Parser $parser) {}

    public function pack(int $protocol_level = 5) {
        return $this->parser -> pack($this->data, $protocol_level);
    }

    public function setCode(int $code): MQTT {
        $this->data['code'] = $code;
        return $this;
    }

    public function setType(int $type): MQTT {
        $this->data['type'] = $type;
        return $this;
    }

    public function setCodes(array $codes): MQTT {
        $this->data['codes'] = $codes;
        return $this;
    }

    public function setSessionPresent($session_present): MQTT {
        $this->data['session_present'] = $session_present;
        return $this;
    }

    public function setProperties(array $properties): MQTT {
        $this->data['properties'] = $properties;
        return $this;
    }

    public function setCmd(int|string $cmd): MQTT {
        $this->data['cmd'] = $cmd;
        return $this;
    }

    public function setMessage(mixed $message): MQTT {
        $this->data['message'] = $message;
        return $this;
    }

}