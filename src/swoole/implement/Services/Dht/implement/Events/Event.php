<?php

namespace iflow\swoole\implement\Services\Dht\implement\Events;

use iflow\swoole\implement\Services\Dht\implement\Parser\Decode;
use iflow\swoole\implement\Services\Dht\implement\Parser\PacketEnum;
use Swoole\Server;

class Event extends \iflow\swoole\implement\Server\Tcp\Events\Event {

    protected array $requestType = [
        'r' => 'response',
        'q' => 'query'
    ];

    public function onPacket(Server $server, string $data, array $clientInfo) {

        if(strlen($data) == 0) return false;
        $data = Decode::decode($data);

        try {
            if(!isset($data['y']) && empty($this->requestType[$data['y']])) return false;
            return call_user_func([ $this, $this->requestType[$data['y']] ], $data ,$server, $clientInfo);
        } catch (\Exception $e) {
        }
    }


    protected function query(array $data, Server $server, array $clientInfo) {
        return PacketEnum::from($data['q']) -> onPacket($data, $server, $clientInfo, $this -> services);
    }


    protected function response (array $data, Server $server, array $clientInfo): array {
        if (!isset($data['r']['nodes']) || !isset($data['r']['nodes'][1])) return [];
        return $this->services -> getNode() -> saveNode(
            $this->services -> getNode() -> decode($data['r']['nodes'])
        );
    }
}
