<?php

namespace iflow\swoole\implement\Services\Elasticsearch;

use iflow\swoole\implement\Services\Elasticsearch\Documents\Docs;
use iflow\swoole\implement\Services\Elasticsearch\Documents\Index;
use iflow\swoole\implement\Services\Elasticsearch\Documents\Mappings;

class Elasticsearch {

    private Config $config;

    public function __construct(string|Config $config = '') {
        $this->config = is_string($config) ? new Config($config) : $config;
    }

    public function indices(): Index {
        return new index($this->config);
    }

    public function mappings(): Mappings {
        return new Mappings($this->config);
    }

    public function docs(): Docs {
        return new Docs($this->config);
    }

    /**
     * @return config
     */
    public function getConfig(): config {
        return $this->config;
    }

}