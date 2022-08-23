<?php

namespace iflow\swoole\implement\Services\Dht\implement\Tasks;

use iflow\Container\Container;
use iflow\swoole\implement\Services\Dht\implement\Parser\Encode;
use iflow\swoole\implement\Services\Dht\implement\Tools\Node;
use iflow\Utils\Tools\Timer;
use Swoole\Server;

class JoinNode {

    protected Node $node;

    protected Server $server;

    protected string|int $id;

    public function join(Server $server, Node $node) {
        $this->node = $node;
        $this->server = $server;

        $this->id = $this->node -> getNodeId();
        Timer::tick($node -> getConfig('auto_find'), function () {
            $this->server -> node -> getSize() <= 0 ? $this->JoinDhtNode() : $this->AutoFindDhtNode();
        });
    }

    protected function JoinDhtNode() {
        foreach ($this->node -> getConfig('bootstrapNodes') as $node) {
            $this->findNode([
                'ip' => gethostbyname($node[0]),
                'p' => $node[1],
            ]);
        }
    }

    protected function AutoFindDhtNode() {
        $node = $this->node -> shift();
        $this->findNode($node, $node['nid']);
    }

    protected function findNode(array $node, int|string $nodeId = '') {
        $mid = $nodeId === '' ? $this->node -> getNeighbor($nodeId, $this->id) : $nodeId;
        $this->send([
            't' => $this->node -> basicTools -> gen_random_string(2),
            'y' => 'q',
            'q' => 'find_node',
            'a' => [
                'id' => $this->id,
                'target' => $mid
            ]
        ], $node);
    }


    protected function send(array $data, array $node) {
        $this->server -> sendto($node['ip'], $node['p'], Encode::encode($data));
    }

}