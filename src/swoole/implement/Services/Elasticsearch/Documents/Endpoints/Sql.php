<?php

namespace iflow\swoole\implement\Services\Elasticsearch\Documents\Endpoints;

use iflow\swoole\implement\Services\Elasticsearch\Tool\Request;

class Sql {

    use Request;

    protected array $acceptType = [
        'csv' => 'text/csv',
        'json' => 'application/json',
        'tsv' => 'text/tab-separated-values',
        'txt' => 'text/plain',
        'yaml' => 'application/yaml',
        'cbor' => 'application/cbor',
        'smile' => 'application/smile'
    ];

    /**
     * @param string $sql
     * @param int $fetch_size
     * @return mixed
     */
    public function translate(string $sql, int $fetch_size = 20): mixed {
        return $this->sendRequest('POST', '_sql/translate', [
            'query' => $sql,
            'fetch_size' => $fetch_size
        ]);
    }

    /**
     * 执行SQL
     * @param string $sql
     * @param int $fetch_size
     * @param string $format
     * @param array $options
     * @return mixed
     */
    public function sql(string $sql, int $fetch_size = 20, string $format = 'json', array $options = []): mixed {
        $options = array_merge([ 'sql' => $sql, 'fetch_size' => $fetch_size ], $options);
        return $this->sendRequest('POST', '_sql?format='.$format,
            $options,
            [ 'Accept' => $this->getAcceptType($format) ]
        );
    }


    protected function getAcceptType(string $type) {
        return $this->acceptType[$type] ?? 'application/json';
    }

}