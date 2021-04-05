<?php


namespace iflow\Swoole\dht\lib;


use iflow\facade\Cache;
use iflow\Utils\basicTools;

class nodes
{

    protected basicTools $basicTools;
    public function __construct(
        protected array $nodes = [],
        protected ?config $config = null
    ) {
        $this->basicTools = new basicTools();
    }

    /**
     * @param array $nodes
     * @return nodes
     * @throws \Exception
     */
    public function setNodes(array $nodes): static
    {
        $nodeInfo = $this->getNodes();
        $this->nodes = array_merge($nodes, $nodeInfo['nodes']);
        $this->getStore() -> set('dhtNodeTables', $this->nodes);
        return $this;
    }

    public function addNodes(node $node): static
    {
        $nodes = $this->getNodes();
        $nodes['nodes'][] = $node;
        $this->getStore() -> set('dhtNodeTables', $nodes);
        return $this;
    }

    /**
     * @return node
     * @throws \Exception
     */
    public function nextNode(): node
    {
        $nodeInfo = $this->getNodes()['nodes'];
        $node = array_shift($nodeInfo);
        $this->getStore() -> set('dhtNodeTables', $nodeInfo);
        return $node;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getNodes(): array
    {
        $this->nodes = $this->getStore() -> get('dhtNodeTables');
        unset($this->nodes['iflow_expired']);
        return [
            'count' => count($this->nodes),
            'nodes' => $this->nodes
        ];
    }

    public function deleteNodes()
    {
        return $this->getStore() -> delete('dhtNodeTables');
    }

    protected function getNodesNumber(int $number = 16): array
    {
        $this->getNodes();
        $nodes = [];
        for ($i = 0; $i < $number; $i++) {
            $nodes[] = $this->nodes[$i];
        }
        return $nodes;
    }

    public function genNeighbor($target, $nid): string {
        return substr($target, 0, 10) . substr($nid, 10, 10);
    }

    public function encodeNodes(): array|string
    {
        if (sizeof($this->nodes) === 0) return $this->nodes;
        $n = '';
        foreach($this->getNodesNumber() as $node) {
            $n .= pack('a20Nn', $node->getNodeId(), ip2long($node->getIp()), $node->getPort());
        }
        return $n;
    }

    protected function getStore()
    {
        return Cache::store($this->config -> getNodeTables()['store']);
    }

    public function deCodeNodes(string $msg): array {
        if((strlen($msg) % 26) != 0)
            return [];

        $nodes = [];
        foreach(str_split($msg, 26) as $s) {
            $r = unpack('a20nid/Nip/np', $s);
            if ($r['nid'] !== null) $nodes[] = new Node($r['nid'], long2ip($r['ip']), $r['p']);
        }
        return $nodes;
    }

}