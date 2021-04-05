<?php


namespace iflow\Swoole\dht\lib\event\client;


use iflow\Swoole\dht\lib\config;
use iflow\Swoole\dht\lib\dhtClient;
use iflow\Swoole\dht\lib\nodes;
use iflow\Swoole\dht\lib\utils\coding\client\Decode;
use Swoole\Server;

class packet
{

    public function __construct(
        public nodes &$nodes,
        protected config $config,
        protected dhtClient $dhtClient
    ) {}

    public function NodeTableMaxCount(): bool
    {
        return $this->nodes -> getNodes()['count'] < $this -> config -> getNodeTables()['maxNumber'];
    }

    public function Packet(Server $server, $data, $clientInfo): bool
    {
        if(strlen($data) == 0) return false;
        $pack = Decode::decode($data);
        if (!isset($pack['y'])) return false;

        if ($pack['y'] == 'r') {
            if ($this->NodeTableMaxCount() && array_key_exists('nodes', $pack['r'])) {
                $this->nodes -> setNodes($this->nodes -> deCodeNodes($pack['r']['nodes']));
            }
        } elseif ($pack['y'] === 'q') {
            $action = $pack['q'];
            if (isset($this-> dhtClient -> dhtEvent[$action]) && method_exists($this-> dhtClient, $action)) {
                call_user_func([$this, $this-> dhtClient -> dhtEvent[$action]], ...[$pack, $clientInfo]);
            }
        }
        return true;
    }
}