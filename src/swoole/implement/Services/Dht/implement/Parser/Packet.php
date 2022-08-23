<?php

namespace iflow\swoole\implement\Services\Dht\implement\Parser;

use iflow\swoole\implement\Services\Dht\Service;
use Swoole\Server;

class Packet {

    public function __construct(
        protected Server $server,
        protected array|string $data,
        protected array $clientInfo,
        protected Service $service
    ) {}

    /**
     * 心跳响应
     * @param array $data
     * @return void
     */
    public function ping(array $data) {
        $id = $data['a']['id'];

        $responseData = [
            't' => $data['t'],
            'y' => 'r',
            'r' => [
                'id' => $this->getId($id)
            ]
        ];

        $this->AppendNode($id) -> send($responseData);
    }

    /**
     * 查询节点
     * @param array $data
     * @return void
     */
    public function findNode(array $data) {
        $id = $data['a']['id'];

        $responseData = [
            't' => $data['t'],
            'y' => 'r',
            'r' => [
                'id' => $this->getId($id),
                'nodes' => $this->service -> getNode() -> encode(
                    $this->service -> getNode() -> getNodes()
                )
            ]
        ];

        $this->AppendNode($id) -> send($responseData);
    }

    /**
     * @param array $data
     * @return mixed|void
     */
    public function getPeers(array $data) {

        $infohash = $data['a']['info_hash'];
        $id = $data['a']['id'];

        $responseData = [
            't' => $data['t'],
            'y' => 'r',
            'r' => [
                'id' => $this->getId($id),
                'nodes' => "",
                'token' => substr($infohash, 0, 2)
            ]
        ];

        return $this -> AppendNode($id) -> send($responseData);
    }


    public function announcePeer(array $data) {

        $infohash = $data['a']['info_hash'];
        $port = $data['a']['port'];
        $token = $data['a']['token'];
        $id = $data['a']['id'];
        $tid = $data['t'];

        // 验证token是否正确
        if (substr($infohash, 0, 2) != $token) return false;

        if (isset($msg['a']['implied_port']) && $msg['a']['implied_port'] != 0) {
            $port = $this->clientInfo['port'];
        }

        if ($port >= 65536 || $port <= 0) {
            return false;
        }

        if ($tid == '') return false;

        $responseData = [
            't' => $data['t'],
            'y' => 'r',
            'r' => [
                'id' => $this->getId($id)
            ],
        ];

        $this->send($responseData);


        return false;

//        return $this->server->task([
//            'callable_params' => [
//                'ip'       => $this->clientInfo['ip'],
//                'port'     => $port,
//                'infohash' => serialize($infohash),
//            ],
//            'call' => [ '', '' ]
//        ]);
    }

    /**
     * 获取本机 节点ID
     * @param $id
     * @return string
     */
    protected function getId($id): string {
        return $this->service -> getNode() -> getLocationId($id);
    }

    /**
     * 返回响应数据
     * @param array $responseData
     * @return void
     */
    public function send(array $responseData): mixed {
        return $this->server -> sendto($this->clientInfo['address'], $this->clientInfo['port'], Encode::encode($responseData));
    }

    /**
     * 追加服务节点
     * @param $id
     * @return $this
     */
    protected function AppendNode($id): static {
        $this->service -> getNode() -> saveNode([
            [
                'nid' => $id,
                'ip' => $this->clientInfo['address'],
                'p' => $this->clientInfo['port'],
                'info' => $this->clientInfo
            ]
        ]);

        return $this;
    }

}