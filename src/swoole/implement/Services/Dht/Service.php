<?php

namespace iflow\swoole\implement\Services\Dht;

use iflow\Container\Container;
use iflow\swoole\implement\Services\Dht\implement\Tasks\JoinNode;
use iflow\swoole\implement\Services\Dht\implement\Tools\Node;
use Swoole\Server;

class Service extends \iflow\swoole\implement\Server\Udp\Service {

    protected Node $node;

    public function start() {
        $this->node = app(Node::class, [
            $this->getNodeConfig()
        ]);
        $this->addEventValues([ 'Start' => 'onStart' ], $this);
        parent::start(); // TODO: Change the autogenerated stub
    }

    protected function getNodeConfig() {
        return $this->config['node'] ?? [
            'node_size' => 200,
            'fields' => [
                [ 'name' => 'host', 'type' => \Swoole\Table::TYPE_STRING, 'size' => 15 ],
                [ 'name' => 'port', 'type' => \Swoole\Table::TYPE_INT ],
                [ 'name' => 'info', 'type' => \Swoole\Table::TYPE_STRING, 'size' => 2048 ]
            ]
        ];
    }

    public function onStart() {
        Container::getInstance() -> get(Server::class) -> node = $this->node;
        $this->SwService -> task([
            'callable' => [
                JoinNode::class, 'join'
            ],
            'callable_params' => [
                [
                    'value' => Server::class,
                    'type' => 'object'
                ],
                [
                    'value' => Node::class,
                    'type' => 'object'
                ]
            ]
        ]);
    }

    /**
     * @return Node
     */
    public function getNode(): Node {
        return $this->node;
    }

}