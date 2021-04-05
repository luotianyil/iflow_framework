<?php


namespace iflow\Swoole\dht\lib;


use iflow\Utils\ArrayTools;
use iflow\Utils\basicTools;

class config
{

    protected ArrayTools $arrayTools;
    public basicTools $basicTools;

    protected string $nodeId = "";

    public function __construct(
        protected array $config = []
    ){
        $this->arrayTools = new ArrayTools($this->config);
        $this->basicTools = new basicTools();
    }

    public function getSwConfig()
    {
        return $this->arrayTools -> offsetGet('swConfig')?:[
            'worker_num' => swoole_cpu_num(),
            'daemonize' => false,
            'dispatch_mode' => 2,
            'max_conn' => 65535,
            'heartbeat_check_interval' => 5,
            'heartbeat_idle_time' => 10,
            'task_max_request' => 0
        ];
    }

    public function getServer()
    {
        return $this->arrayTools -> offsetGet('server')?:[
            '127.0.0.1',
            6881
        ];
    }

    public function getBootstrapNodes(): array
    {
        $nodes = $this->arrayTools -> offsetGet('bootstrapNodes')?:[
            ['router.bittorrent.com', 6881],
            ['dht.transmissionbt.com', 6881],
            ['router.utorrent.com', 6881]
        ];

        $object = [];
        $nodeId = $this->genNodeId();
        foreach ($nodes as $node) {
            $object[] = new node($nodeId, $node[0], $node[1]);
        }
        return $object;
    }

    public function getNodeTables()
    {
        return $this->arrayTools -> offsetGet('nodeTables') ?: [
            'store' => 'dht',
            'maxNumber' => 30000
        ];
    }

    public function genNodeId(): string
    {
        $this->nodeId =
            $this->nodeId ?: sha1($this->basicTools -> gen_random_string(), true);
        return $this->nodeId;
    }

    public function getHandle()
    {
        return $this->arrayTools -> offsetGet('handle') ?: '';
    }

}