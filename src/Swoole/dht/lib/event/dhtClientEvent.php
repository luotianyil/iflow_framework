<?php


namespace iflow\Swoole\dht\lib\event;

use iflow\Swoole\dht\lib\event\client\packet;
use iflow\Swoole\dht\lib\node;
use iflow\Swoole\dht\lib\nodes;
use iflow\Swoole\dht\lib\utils\coding\client\Encode;
use Swoole\Coroutine\Socket;

trait dhtClientEvent
{
    protected nodes $nodes;
    protected array $bootstrapNode = [];
    protected ?Socket $socket = null;
    protected packet $packet;
    protected string $nodeId = "";
    protected string $lastIp = "";

    public array $dhtEvent = [
        'ping' => 'ping',
        'find_node' => 'findNode',
        'get_peers' => 'getPeers',
        'announce_peer' => 'announcePeer'
    ];

    public function send($msg, array $options = []): bool
    {
        if(!filter_var($options[0], FILTER_VALIDATE_IP)) return false;
        $ip = $options[0];
        $data = Encode::encode($msg);
        return $this-> server->sendto($ip, $options[1], $data);
    }

    public function socketSend($msg, $ip, $port): bool
    {
        if ($this->socket === null) {
            $this->socket = new Socket(2, 2, 0);
        }

        $send = $this->socket -> sendto($ip, $port, $msg);
        $send = is_int($send);
        if ($send) {
            $pack = $this->socket -> recv();
            $this->packet -> Packet($this->server, $pack, [
                $ip, $port
            ]);
        }
        return $send;
    }

    public function Receive($serv, $fd, $from_id, $data)
    {}

    protected function ping($pack, $address)
    {
        $id = $pack['a']['id'];
        $this->nodes -> addNodes(new node($id, $address[0], $address[1]));
        $this->send([
            't' => $pack['t'],
            'y' => 'r',
            'r' => [
                'id' => $this->nodes ->genNeighbor($id, $this->nodeId)
            ]
        ], $address);
    }

    protected function findNodes($pack, $address)
    {
        $this->send([
            't' => $pack['t'],
            'y' => 'r',
            'r' => [
                'id' => $this->nodes -> genNeighbor($pack['a']['id'], $this->nodeId),
                'nodes' => $this->nodes -> encodeNodes()
            ]
        ], $address);
    }

    protected function getPeers($pack, $address)
    {
        $infoHash = $pack['a']['info_hash'];
        $id = $pack['a']['id'];

        $this->nodes -> addNodes(new node($id, $address[0], $address[1]));
        $this -> callBack($pack);
        $this->send([
            't' => $pack['t'],
            'y' => 'r',
            'r' => [
                'id' =>  $this->nodes -> genNeighbor($id, $this->nodeId),
                'nodes' => "",
                'token' => substr($infoHash, 0, 2)
            ]
        ], $address);
    }

    protected function announcePeer($pack, $address): bool
    {
        $infoHash = $pack['a']['info_hash'];
        $port = $pack['a']['port'];
        $token = $pack['a']['token'];

        if (substr($infoHash, 0, 2) != $token) return false;

        if (isset($pack['a']['implied_port']) && $pack['a']['implied_port'] != 0) $port = $address[1];

        if ($port >= 65536 || $port <= 0) return false;

        if($address[0] == $this->lastIp) return false;
        $this->lastIp = $ip = $address[0];

        $this->send([
            't' => $pack['t'],
            'y' => 'r',
            'r' => [
                'id' => $this->nodeId
            ]
        ], $address);

        return $this->server -> task([
            'ip' => $ip,
            'port' => $port,
            'infohash' => $infoHash
        ]);
    }

    protected function joinDht() {
        foreach ($this->bootstrapNode as $node) {
            $this->findNode([
                $node -> getIp(),
                $node -> getPort()
            ], $node -> getNodeId());
        }
    }

    protected function autoFindNode() {
        if ($this->NodeTableMaxCount()) {
            $node = $this->nodes -> nextNode();
            $this->findNode([
                $node -> getIp(),
                $node -> getPort()
            ], $node -> getNodeId());
        }
    }

    protected function findNode(array $address, string $nodeId) {
        $this->send([
            't' => $this -> dht ->config->basicTools->gen_random_string(2),
            'y' => 'q',
            'q' => 'find_node',
            'a' => [
                'id' => $nodeId,
                'target' => $this->nodes -> genNeighbor($this->nodeId, $nodeId)
            ]
        ], $address);
    }

    protected function getPeer(string $hash = "", array $address = [])
    {
        $this->send([
            't' => $this -> dht ->config->basicTools->gen_random_string(2),
            'y' => 'q',
            'q' => 'get_peers',
            'a' => [
                'id' => $this->nodeId,
                'info_hash' => $hash
            ]
        ], $address);
    }

    protected function NodeTableMaxCount(): bool
    {
        return $this->packet -> NodeTableMaxCount();
    }

    protected function callBack($data)
    {
        $class = $this->dht -> config -> getHandle();
        if (class_exists($class)) {
            app() -> make($class) -> handle($data);
        }
    }
}