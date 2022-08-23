<?php

namespace iflow\swoole\implement\Server\Mqtt\Packet;

class MQTT {

    protected array $data = [];

    public function __construct( protected Parser $parser ) {}

    public function pack(int $protocol_level = 5) {
        return $this->parser -> pack($this->data, $protocol_level);
    }

    public function setCode(int $code): static {
        $this->data['code'] = $code;
        return $this;
    }

    public function setType(int $type): static {
        $this->data['type'] = $type;
        return $this;
    }

    public function setCodes(array $codes): static {
        $this->data['codes'] = $codes;
        return $this;
    }

    public function setSessionPresent($session_present): static {
        $this->data['session_present'] = $session_present;
        return $this;
    }

    public function setCmd(int|string $cmd): static {
        $this->data['cmd'] = $cmd;
        return $this;
    }

    public function setMessage(mixed $message): static {
        $this->data['message'] = $message;
        return $this;
    }

}