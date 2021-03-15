<?php


namespace iflow\log\lib\channels;


class elasticsearch
{

    protected array $config = [];

    protected \iflow\Swoole\Elasticsearch\elasticsearch $elClient;

    public function __construct(array $config = [])
    {
        $this->elClient = new \iflow\Swoole\Elasticsearch\elasticsearch($config['elasticsearchConfigName']);
        $this->config = config('elasticsearch@connections.'.$config['elasticsearchConfigName']);
    }

    public function save(array $logs)
    {
        return !$this->indicesExits() -> write($logs)['errors']??true;
    }

    private function indicesExits(): static
    {
        if ($this->elClient -> indices() -> indicesExists($this->config['index_name'])) {
            $this->elClient -> indices() -> createIndices($this->config['index_name']);
            $this->elClient -> mappings()
                -> setMappingsOptions([
                    'properties' => [
                        'time' => [
                            'type' => 'date',
                            'format' => 'yyyy-MM-dd HH:mm:ss'
                        ],
                        'content' => [
                            'type' => 'text',
                            'fielddata' => true
                        ],
                        'type' => [
                            'type' => 'text',
                            'fielddata' => true
                        ]
                    ]
                ])
                -> setMappings(
                    $this->config['index_name'],
                    $this->config['type_name']
                );
        }
        return $this;
    }

    private function write(array $logs) {
        return $this->elClient -> docs() -> createDoc($logs, $this->config['index_name'],
            $this->config['type_name']);
    }

}