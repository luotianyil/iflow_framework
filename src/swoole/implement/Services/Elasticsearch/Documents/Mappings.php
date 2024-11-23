<?php

namespace iflow\swoole\implement\Services\Elasticsearch\Documents;

use iflow\swoole\implement\Services\Elasticsearch\Tool\Request;

class Mappings {

    use Request;

    /**
     * 设置字段
     * @param string $indexName
     * @param string $typeName
     * @return mixed
     */
    public function setMappings(string $indexName, string $typeName = ''): array {
        $url = sprintf('%s%s/_mapping', $indexName, $this->getTypeName($typeName));
        return $this->sendRequest('PUT', $url, $this->mappingsOptions);
    }

    /**
     * 获取Mapping
     * @param string $indexName
     * @param string $typeName
     * @return mixed
     */
    public function getMappings(string $indexName, string $typeName = ''): array {
        return $this->sendRequest('GET',
            sprintf("%s%s/_mapping?include_type_name=true", $indexName, $this->getTypeName($typeName))
        );
    }

    /**
     * 查看指定字段类型
     * @param string $indexName
     * @param string $fields
     * @param string $typeName
     * @return mixed
     */
    public function getFieldMapping(string $indexName, string $fields, string $typeName = ''): array {
        return $this->sendRequest('GET',
            sprintf("%s%s/_mapping/%s?include_type_name=true", $indexName, $this->getTypeName($typeName), $fields)
        );
    }

    /**
     * 设置索引类型
     * @param array $data
     * @param string $indexName
     * @param string $typeName
     * @return array
     */
    public function mappingSetting(array $data, string $indexName, string $typeName = ''): array {
        return $this->sendRequest('PUT',
            sprintf("%s%s/_settings", $indexName, $this->getTypeName($typeName)), $data
        );
    }

}