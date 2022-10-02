<?php

namespace iflow\swoole\implement\Services\Dht\implement\Tools;

use iflow\Container\Container;
use iflow\Container\implement\generate\exceptions\InvokeClassException;
use iflow\Helper\Str\Str;
use iflow\swoole\implement\Tools\Tables;
use Swoole\Table;

class Node {

    protected Table $table;

    protected array $nodeKeys = [];

    protected string $nodeId = '';


    /**
     * @throws InvokeClassException
     */
    public function __construct(protected array $config) {
        $this->createNodeTable();
    }

    public function getNodeId(): string {
        return $this->nodeId = $this->nodeId ?: sha1(Str::RandomStr());
    }

    public function getNeighbor($target, $nid): string {
        return substr($target, 0, 10) . substr($nid, 10, 10);
    }

    /**
     * 获取本机Id
     * @param string|int $id
     * @return string
     */
    public function getLocationId(string|int $id): string {
        return $this -> getNeighbor($id, $this -> getNodeId());
    }

    /**
     * 创建节点存储数据表结构
     * @return void
     * @throws InvokeClassException
     */
    protected function createNodeTable() {
        $this->table = Container::getInstance() -> make(Tables::class) -> get('dht', true);
        foreach ($this->config['fields'] as $field) {
            $this->table -> column($field['name'], $field['type'], $field['size'] ?? 0);
        }
        $this->table -> create();
    }

    /**
     * 获取节点数据
     * @param int $offset
     * @return array
     */
    public function getNodes(int $offset = 16): array {
        $node = [];
        foreach($this->table as $row)  {
            $node = $row;
            if (count($node) === $offset) break;
        }
        return $node;
    }

    /**
     * 存储节点
     * @param array $nodes
     * @return array
     */
    public function saveNode(array $nodes): array {
        foreach ($nodes as $node) {
            if ($this->table -> exist($node['id'])) {
                continue;
            }

            $node['index'] = $this->table -> count();
            $this->table -> set($node['id'], $node);

            if ($this->table -> count() > $this->config['node_size']) {
                $this->table -> delete(array_shift($this->nodeKeys));
            }
        }

        return $nodes;
    }

    public function decode(string $nodes): array {
        if((strlen($nodes) % 26) != 0) return [];

        $n = [];
        foreach(str_split($nodes, 26) as $s) {
            $node = unpack('a20nid/Nip/np', $s);
            $node['ip'] = long2ip($node['ip']);
            $n[] = $node;
        }
        return $n;
    }

    public function encode(array $nodes): string {

        if(count($nodes) == 0)  return '';

        $n = '';
        foreach($nodes as $node)
            $n .= pack('a20Nn', $node['nid'], ip2long($node['ip']), $node['port']);

        return $n;
    }


    /**
     * @param string $key
     * @return mixed
     */
    public function getConfig(string $key = ''): mixed {
        return  $key !== '' ? $this->config[$key] : $this->config;
    }


    /**
     * 获取当前节点数量
     * @return int
     */
    public function getSize(): int {
        return $this->table -> count();
    }


    public function shift() {
        return $this->table -> get(array_shift($this->nodeKeys));
    }
}