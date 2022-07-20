<?php

namespace iflow\swoole\implement\Services\Elasticsearch\Documents;

use iflow\swoole\implement\Services\Elasticsearch\Tool\Request;

class Mappings {

    use Request;

    public function setMappings($indexName, $typeName) {
        return $this->sendRequest('PUT',
            sprintf('%s/%s/_mapping?include_type_name=true', $indexName, $typeName), $this->mappingsOptions);
    }

    public function getMappings($indexName, $typeName) {
        return $this->sendRequest('GET',
            sprintf("%s/%s/_mapping?include_type_name=true", $indexName, $typeName)
        );
    }

}