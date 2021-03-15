<?php


namespace iflow\Swoole\Elasticsearch\lib;


class index
{
    use connection;

    public function getIndices(string $indexName = '')
    {
        return $this->sendRequest('GET', $indexName);
    }

    public function createIndices(string $indexName)
    {
        return $this->sendRequest('PUT', $indexName, $this->indicesOptions);
    }

    public function deleteIndices(string $indexName)
    {
        return $this->sendRequest('DELETE', $indexName);
    }

    public function indicesExists(string $indexName): bool
    {
        $indices = $this->sendRequest('GET', $indexName);
        return empty($indices['status']);
    }
}