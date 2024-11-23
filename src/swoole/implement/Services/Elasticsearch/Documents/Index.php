<?php

namespace iflow\swoole\implement\Services\Elasticsearch\Documents;

use iflow\swoole\implement\Services\Elasticsearch\Tool\Request;

class Index {

    use Request;

    public function getIndices(string $indexName = '') {
        return $this->sendRequest('GET', $indexName);
    }

    public function createIndices(string $indexName) {
        return $this->sendRequest('PUT', $indexName, $this->indicesOptions);
    }

    public function deleteIndices(string $indexName) {
        return $this->sendRequest('DELETE', $indexName);
    }

    public function indicesExists(string $indexName): bool {
        $indices = $this->sendRequest('GET', $indexName);
        return empty($indices['error']);
    }

    public function indicesSetting(string $indexName, array $_settings) {
        return $this->sendRequest('PUT',
            sprintf("%s/_settings", $indexName),
            $_settings
        );
    }

}