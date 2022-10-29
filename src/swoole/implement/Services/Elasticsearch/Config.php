<?php

namespace iflow\swoole\implement\Services\Elasticsearch;

use Exception;
use iflow\swoole\implement\Services\Elasticsearch\Documents\Docs;
use iflow\swoole\implement\Services\Elasticsearch\Documents\Endpoints\Sql;
use iflow\swoole\implement\Services\Elasticsearch\Documents\Index;
use iflow\swoole\implement\Services\Elasticsearch\Documents\Mappings;

class Config {

    private array $config;

    protected readonly array $documentsMappings;

    public function __construct(string $name)
    {
        $config = config('elasticsearch');
        $name = $name === '' ? $config['default'] : $name;
        $this->config = $config['connections'][$name];
    }

    public function getHost(): string
    {
        $url = explode(':', $this->config['host']);
        return "${url[0]}:${url[1]}";
    }

    public function getPort(): int
    {
        return (int)explode(':', $this->config['host'])[2] ?? 9200;
    }

    public function getIndexName(): string {
        return $this->config['index_name'] ?? 'test';
    }

    public function getTypeName(): string {
        return $this->config['type_name'] ?? 'test';
    }

    public function getUserName(): string {
        return $this->config['user_name'] ?? '';
    }

    public function getPassWord(): string
    {
        return $this->config['pass_word'] ?? '';
    }

    public function setApiKey(string $id = '', string $apiKey = ''): static
    {
        $id = $id ?: $this->getUserName();
        $apiKey = $apiKey?: $this->getPassWord();

        if (!$id || !$apiKey) return $this;
        $this->config['headers']['Authorization'] = 'Basic '.base64_encode($id . ':' . $apiKey);
        return $this;
    }

    public function getHeader(): array
    {
        if (empty($this->config['headers']['Authorization'])) {
            $this->setApiKey();
        }

        return array_merge($this->config['headers'] ?? [], [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json, text/javascript, */*; q=0.01',
//            'Accept-Encoding' => 'gzip',
            'User-Agent' => sprintf(
                "iflow-elasticsearch-php/%s (%s %s; PHP %s)",
                '0.0.1',
                PHP_OS,
                php_uname(),
                phpversion()
            ),
            'x-elastic-client-meta' => $this->getElsMate()
        ]);
    }

    public function getOptions()
    {
        return $this->config['options'];
    }

    private function getElsMate(): string {
        $phpSemVersion = sprintf("%d.%d.%d", PHP_MAJOR_VERSION, PHP_MINOR_VERSION, PHP_RELEASE_VERSION);
        // Reduce the size in case of '-snapshot' version
        $clientVersion = str_replace('-snapshot', '-s', '0.0.1');
        return sprintf(
            "es=%s,php=%s,t=%s,a=%d",
            $clientVersion,
            $phpSemVersion,
            $clientVersion,
            1
        );
    }

    public function getRequestUrl(string $uri): string {
        return sprintf("%s:%s/%s", $this -> getHost(), $this -> getPort(), $uri);
    }

    /**
     * 自定义实现类
     * @param string $name
     * @return array|string
     * @throws Exception
     */
    public function getDocumentsMappings(string $name = ''): array|string {

        if (empty($this->documentsMappings)) {
            $this->documentsMappings = array_merge([
                'indices' => Index::class,
                'mappings' => Mappings::class,
                'docs' => Docs::class,
                'sql' => Sql::class
            ], array_change_key_case($this->config['documentsMappings'] ?? [], CASE_LOWER));
        }

        if ($name === '') return $this->documentsMappings;

        return $this->documentsMappings[$name] ?: throw new Exception('Elasticsearch driver does not exist');
    }
}