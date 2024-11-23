<?php

namespace iflow\swoole\implement\Services\Elasticsearch;

use iflow\swoole\implement\Services\Elasticsearch\Documents\Docs;
use iflow\swoole\implement\Services\Elasticsearch\Documents\Endpoints\Sql;
use iflow\swoole\implement\Services\Elasticsearch\Documents\Index;
use iflow\swoole\implement\Services\Elasticsearch\Documents\Mappings;

/**
 * @method Index indices
 * @method Mappings mappings
 * @method Docs docs
 * @method Sql sql
 */
class Elasticsearch {

    private Config $config;

    public function __construct(string|Config $config = '') {
        $this->config = is_string($config) ? new Config($config) : $config;
    }

    /**
     * @return config
     */
    public function getConfig(): config {
        return $this->config;
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     * @throws \Exception
     */
    public function __call(string $name, array $arguments): mixed {
        // TODO: Implement __call() method.
        $driver = $this->config -> getDocumentsMappings($name);
        return new $driver($this->config);
    }

}