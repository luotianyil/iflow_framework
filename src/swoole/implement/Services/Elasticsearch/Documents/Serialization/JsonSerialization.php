<?php

namespace iflow\swoole\implement\Services\Elasticsearch\Documents\Serialization;

use JsonException;

class JsonSerialization {


    /**
     * @throws JsonException
     */
    public function bulkJsonSerialization(
        array $docs, string $index, string $typeName = '_doc', string $type = 'create'
    ): string {

        if (empty($docs)) return "";

        $docsParams = [ 'body' => [] ];
        foreach ($docs as $doc) {
            if ($type !== 'query') {
                $docsParams['body'][] = [ $type => $this->getBulkType($index, $typeName, $doc['_id'] ?? '') ];
                if (isset($doc['_id'])) unset($doc['_id']);
            }

            if ($type !== 'delete') {
                $docsParams['body'][] = $type === 'update' ? [
                    'doc' => $doc
                ] : ($type === 'query' && empty($doc) ? '{}' :  $doc);
            }
        }

        $body = "";
        foreach ($docsParams['body'] as $docsParam) {
            $body .= is_string($docsParam) ? "$docsParam\n" : json_encode($docsParam, JSON_PRESERVE_ZERO_FRACTION + JSON_INVALID_UTF8_SUBSTITUTE + JSON_THROW_ON_ERROR) . "\n";
        }

        return $body;
    }

    /**
     *
     * @param string $index
     * @param string $typeName
     * @param string|int $id
     * @return array
     */
    protected function getBulkType(
        string $index, string $typeName = '_doc', string|int $id = ''
    ): array {
        $types = [ '_index' => $index ];


        if ($typeName !== '' && $typeName !== '_doc') $types['_type'] = $typeName;
        if ($id !== '') $types['_id'] = $id;

        return $types;
    }

}