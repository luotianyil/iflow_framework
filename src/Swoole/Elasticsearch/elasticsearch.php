<?php


namespace iflow\Swoole\Elasticsearch;


use iflow\Swoole\Elasticsearch\lib\config;
use iflow\Swoole\Elasticsearch\lib\docs;
use iflow\Swoole\Elasticsearch\lib\index;
use iflow\Swoole\Elasticsearch\lib\mappings;

class elasticsearch
{
    private config $config;

    public function __construct(string|config $config = '')
    {
        $this->config = is_string($config) ? new config($config) : $config;
    }

    public function indices(): index
    {
        return new index($this->config);
    }

    public function mappings(): mappings
    {
        return new mappings($this->config);
    }

    public function docs(): docs
    {
        return new docs($this->config);
    }
}