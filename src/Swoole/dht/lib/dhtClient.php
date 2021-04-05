<?php


namespace iflow\Swoole\dht\lib;

use iflow\Swoole\dht\lib\event\client\packet;
use iflow\Swoole\dht\lib\utils\Metadata;
use Swoole\Client;
use Swoole\Coroutine\Socket;
use Swoole\Server;
use iflow\Swoole\dht\lib\event\dhtClientEvent;

class dhtClient extends dhtBase
{

    use dhtClientEvent;

    protected ?Socket $socket = null;

    protected function run(): static
    {
        // TODO: Implement run() method.

        $this->bootstrapNode = $this->dht->config->getBootstrapNodes();
        $this->nodeId = $this->dht->config->genNodeId();
        $this->nodes = new nodes(config: $this->dht->config);
        $this->server = new Server('0.0.0.0', 0, SWOOLE_PROCESS, SWOOLE_SOCK_UDP);
        $this->packet = new packet($this->nodes, $this->dht->config, $this);

        $this->bindEvent($this, [
            'Receive' => 'Receive',
            'task' => 'task'
        ]);

        $this->server->on('start', function (Server $server) {
            $this->nodes->deleteNodes();
            $server->tick(3000, function () {
                $nodes = $this->nodes->getNodes();
                if ($this->NodeTableMaxCount() && $nodes['count'] === 0) $this->joinDht();
                else $this->autoFindNode();
            });
        });

        $this->server->on('packet', function () {
            $this->packet->Packet(...func_get_args());
        });
        return $this;
    }

    public function task(Server $server, $task_id, $reactor_id, $data)
    {
        $ip = $data['ip'];
        $port = $data['port'];
        $infoHash = $data['infohash'];
        $client = new Client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_SYNC);
        if ($client->connect($ip, $port, 3)){
            $meta = new Metadata($client, $infoHash, $this -> dht -> config);
            $this->callBack($meta -> downloadMetaData());
        }
        $client -> close();
    }
}