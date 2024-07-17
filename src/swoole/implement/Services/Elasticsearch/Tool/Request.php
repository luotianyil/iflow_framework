<?php

namespace iflow\swoole\implement\Services\Elasticsearch\Tool;

use iflow\swoole\implement\Services\Elasticsearch\Config;
use iflow\swoole\implement\Services\Elasticsearch\Documents\Mappings;
use iflow\swoole\implement\Services\Elasticsearch\Documents\Serialization\JsonSerialization;

trait Request {

    public array $mappingsOptions = [];

    protected array $indicesOptions = [
        'settings' => [
            'index' => [
                'number_of_shards' => 4,
                'number_of_replicas' => 0,
                'blocks.read_only_allow_delete' => false
            ]
        ]
    ];

    private array $docQuery = [
        'query' => [
            'bool' => [
                'must' => [],
                'must_not' => [],
                'should' => []
            ]
        ],
        'from' => 0,
        'sort' => [],
        'size' => 50,
        'version' => true
    ];

    public function __construct(
        private Config $config
    ){}

    public function setIndicesOptions($options = []): static {
        $this->indicesOptions = array_replace_recursive($this->indicesOptions, $options) ?: [];
        return $this;
    }

    /**
     * @param array $docQuery
     * @return static
     */
    public function setDocQuery(array $docQuery): static {
        $this->docQuery = array_replace_recursive($this->docQuery, $docQuery)?: [];
        return $this;
    }

    /**
     * @param array $mappingsOptions
     * @return Mappings
     */
    public function setMappingsOptions(array $mappingsOptions): static {
        $this->mappingsOptions = $mappingsOptions;
        return $this;
    }

    /**
     * @throws \JsonException
     */
    public function bulk(array $docs, string $indexName, string $typeName, string $type = 'create'): string|null
    {
        return (new JsonSerialization()) -> bulkJsonSerialization(
            $docs, $indexName, $typeName, $type
        );
    }

    public function getTypeName(string $typeName): string {
        return $typeName === '' ? '' : '/'.$typeName;
    }

    private function sendRequest($methods, $uri, array|string $params = [], array $header = [])
    {
        if (is_array($params) && count($params) === 0) {
            $params = '';
        }

        return httpRequest(
            $this->config -> getRequestUrl($uri),
            $methods,
            array_merge($this->config -> getHeader(), $header),
            [ 'type' => 'body', 'parameters' => $params ],
            options: $this->config -> getOptions()
        ) -> getResponseBodyType() -> getParserContent();
    }

}