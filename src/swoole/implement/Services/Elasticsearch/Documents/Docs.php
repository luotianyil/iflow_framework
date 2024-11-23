<?php

namespace iflow\swoole\implement\Services\Elasticsearch\Documents;

use iflow\swoole\implement\Services\Elasticsearch\Tool\Request;

class Docs {

    use Request;

    public function createDoc(array $docs, string $indexName, string $typeName = '')
    {
        $docs = $this->bulk($docs, $indexName, $typeName);
        if ($docs) return $this->sendRequest('POST',"_bulk", $docs);
        return false;
    }

    public function deleteDocs(string $indexName, string $docId, string $typeName = '')
    {
        return $this->sendRequest('DELETE', sprintf("%s%s/%s", $indexName, $this->getTypeName($typeName), $docId));
    }

    public function deleteDocsBulk(array $docs, string $indexName, string $typeName = '')
    {
        $docs = $this->bulk($docs, $indexName, $typeName, 'delete');
        if ($docs) return $this->sendRequest('POST',"_bulk", $docs);
        return false;

    }

    /**
     * 按照查询删除
     * @param array $query
     * @param string $indexName
     * @param string $typeName
     * @return mixed
     */
    public function deleteDocsByQuery(array $query, string $indexName, string $typeName = ''): mixed {
        return $this->sendRequest('POST', sprintf("%s%s/_delete_by_query", $indexName, $typeName ? '/'.$typeName : ''), $query);
    }

    public function updateDocsBulk(array $docs, string $indexName, string $typeName = '')
    {
        $docs = $this->bulk($docs, $indexName, $typeName, 'update');
        if ($docs) return $this->sendRequest('POST',"_bulk", $docs);
        return false;
    }

    /**
     * 按照查询更新
     * @param array $query 查询参数
     * @param string $indexName 索引名称
     * @param string $typeName 索引类型
     * @param string $conflicts 冲突处理 conflicts=proceed
     * @return mixed
     */
    public function updateDocByQuery(array $query, string $indexName, string $typeName = '', string $conflicts = ''): mixed {
        return $this->sendRequest('POST', sprintf(
            "%s%s/_update_by_query%s",
            $indexName, $typeName ? '/'.$typeName : '',
            $conflicts ? '?conflicts='.$conflicts : ''
        ), $query);
    }

    public function getDocs(string $indexName, string $typeName = '_doc', string $docId = '_search')
    {
        return $this->sendRequest('GET', sprintf("%s/%s/%s", $indexName, $typeName, $docId));
    }

    /**
     * 批量获取
     * @param array $query
     * @param string $indexName
     * @param string $typeName
     * @return mixed
     */
    public function mGetDocs(array $query, string $indexName = '', string $typeName = ''): mixed {
        $body = array_key_exists('docs', $query) ? $query : [ 'ids' => $query ];
        return $this->sendRequest('GET', sprintf("%s/%s/_mget", $indexName, $typeName), $body);
    }

    /**
     * 判断文档存在
     * @param string $indexName
     * @param string $typeName
     * @param string $docId
     * @return bool
     */
    public function existsDoc(string $indexName, string $docId, string $typeName = '_doc'): bool
    {
        return !empty($this->getDocs(...func_get_args())['found']);
    }

    /**
     * 搜索文档
     * @param string $indexName
     * @param string $typeName
     * @return mixed
     */
    public function searchDoc(string $indexName, string $typeName = '_doc'): mixed {
        return $this->sendRequest('GET', sprintf("%s%s/_search", $indexName, $typeName ? '/'.$typeName : ''), $this->docQuery);
    }

    public function postSearchDoc(string $indexName, string $typeName = '_doc'): mixed {
        return $this->sendRequest('POST', sprintf("%s%s/_search", $indexName, $typeName ? '/'.$typeName : ''), $this->docQuery);
    }

    public function multiSearchDoc(string $indexName, string $typeName = ''): mixed {
        $docs = $this->bulk($this->queryParams, $indexName, $typeName, 'query');
        if ($docs) return $this->sendRequest('GET',sprintf("%s%s/_msearch", $indexName, $typeName ? '/'.$typeName : ''), $docs);
        return false;
    }


    public function countDoc(string $indexName, string $typeName = '', bool $isRefresh = false): mixed {
        return $this->sendRequest($isRefresh ? 'POST' : 'GET',
            sprintf("%s%s/_count%s", $indexName, $typeName ? '/'.$typeName : '', $isRefresh ? '?refresh' : ''),
            $this->queryParams
        );
    }

    public function refresh(string $indexName, string $typeName = ''): mixed {
        return $this->sendRequest('POST',
            sprintf("%s%s/_refresh", $indexName, $typeName ? '/'.$typeName : '')
        );
    }

}