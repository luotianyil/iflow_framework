<?php


namespace iflow\Swoole\Elasticsearch\lib;


class config
{
    private array $config;

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
        return (int)explode(':', $this->config['host'])[2] ?: 9200;
    }

    public function getIndexName(): string
    {
        return $this->config['index_name'] ?: 'test';
    }

    public function getTypeName(): string
    {
        return $this->config['type_name'] ?: 'test';
    }

    public function getUserName(): string
    {
        return $this->config['user_name'] ?: '';
    }

    public function getPassWord(): string
    {
        return $this->config['pass_word'] ?: '';
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
            'Accept' => 'text/html,application/xhtml+xml,application/xml,application/json;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
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

    private function getElsMate() {
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
}