<?php


namespace iflow\log\implement\channels;

use iflow\swoole\implement\Services\Elasticsearch\Elasticsearch as ES;

class Elasticsearch {

    protected array $config = [];

    protected ES $elClient;

    public function __construct(array $config = []) {
        $this->elClient = new ES($config['elasticsearchConfigName']);
        $this->config = config('elasticsearch@connections.'.$config['elasticsearchConfigName']);
    }

    public function save(array $logs): bool {
        return !$this->indicesExits() -> write($logs)['errors'] ?? true;
    }

    protected function indicesExits(): static {
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
                            'type' => 'string',
                            'fielddata' => true
                        ]
                    ]
                ])
                -> setMappings($this->config['index_name']);
        }
        return $this;
    }

    protected function write(array $logs) {
        return $this->elClient -> docs() -> createDoc($logs, $this->config['index_name'], $this->config['type_name']);
    }

}