<?php

namespace iflow\swoole\implement\Services\Elasticsearch\Tool;

use iflow\swoole\implement\Services\Elasticsearch\Config;
use iflow\swoole\implement\Services\Elasticsearch\Documents\Mappings;

trait Request {

    public array $mappingsOptions = [
        'properties' => [
            'id' => [
                'type' => 'integer'
            ],
            'name' => [
                'type' => 'text',
                'fielddata' => true
            ]
        ]
    ];

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

    public function setIndicesOptions($options = []): static
    {
        $this->indicesOptions = array_replace_recursive($this->indicesOptions, $options) ?: [];
        return $this;
    }

    /**
     * @param array $docQuery
     * @return static
     */
    public function setDocQuery(array $docQuery): static
    {
        $this->docQuery = array_replace_recursive($this->docQuery, $docQuery)?: [];
        return $this;
    }

    /**
     * @param array $mappingsOptions
     * @return Mappings
     */
    public function setMappingsOptions(array $mappingsOptions): static
    {
        $this->mappingsOptions = array_replace_recursive($this->mappingsOptions, $mappingsOptions)?: [];
        return $this;
    }

    public function bulk(array $docs, string $indexName, string $typeName, string $type = 'create'): string|null
    {
        if ($docs) {
            $params['body'] = [];
            foreach ($docs as $key => $value) {
                $params['body'][] = [
                    $type => [
                        '_index' => $indexName,
                        '_type' => $typeName,
                        '_id' => $value['_id'] ?? uniqid('_id')
                    ]
                ];

                if ($type !== 'delete') {
                    unset($value['_id']);
                    $params['body'][] = $type === 'update' ? [
                        'doc' => $value
                    ] : $value;
                }
            }

            $body = "";
            foreach ($params['body'] as $val) {
                $body .= json_encode($val) . "\r\n";
            }
            return $body;
        }
        return null;
    }

    private function sendRequest($methods, $uri, array|string $params = [], array $header = [])
    {
        if (is_array($params) && count($params) === 0) {
            $params = "";
        }
        return httpRequest(
            $this->config -> getRequestUrl($uri),
            $methods,
            array_merge($header, $this->config -> getHeader()),
            [ 'type' => 'json', 'data' => $params ],
            options: $this->config -> getOptions()
        ) -> getResponseBodyType() -> getParserContent();
    }

}