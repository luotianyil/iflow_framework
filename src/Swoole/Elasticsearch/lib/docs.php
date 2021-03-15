<?php


namespace iflow\Swoole\Elasticsearch\lib;


class docs
{

    use connection;
    public function createDoc($docs, string $indexName, string $typeName)
    {
        $docs = $this->bulk($docs, $indexName, $typeName);
        if ($docs) return $this->sendRequest('POST',"_bulk", $docs);
        return false;
    }

    public function deleteDocs(string $indexName, string $typeName, string $docId)
    {
        return $this->sendRequest('DELETE',
        sprintf("%s/%s/%s", $indexName, $typeName, $docId));
    }

    public function deleteDocsBulk(array $docs, string $indexName, string $typeName)
    {
        $docs = $this->bulk($docs, $indexName, $typeName, 'delete');
        if ($docs) return $this->sendRequest('POST',"_bulk", $docs);
        return false;

    }

    public function updateDocsBulk(array $docs, string $indexName, string $typeName)
    {
        $docs = $this->bulk($docs, $indexName, $typeName, 'update');
        if ($docs) return $this->sendRequest('POST',"_bulk", $docs);
        return false;
    }

    public function getDocs(string $indexName, string $typeName, string $docId = '_search')
    {
        return $this->sendRequest('GET', sprintf("%s/%s/%s", $indexName, $typeName, $docId));
    }

    public function mGetDocs(array $docIds, string $indexName, string $typeName)
    {
        return $this->sendRequest('GET', sprintf("%s/%s/_mget", $indexName, $typeName), [
            "ids" => $docIds
        ]);
    }

    /**
     * 判断文档存在
     * @param string $indexName
     * @param string $typeName
     * @param string $docId
     * @return bool
     */
    public function existsDoc(string $indexName, string $typeName, string $docId): bool
    {
        return !empty($this->getDocs(...func_get_args())['found']);
    }

    public function searchDoc(string $indexName, string $typeName)
    {
        return $this->sendRequest('GET',
            sprintf("%s/%s/_search", $indexName, $typeName),
            $this->docQuery
        );
    }

}