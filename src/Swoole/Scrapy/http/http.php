<?php


namespace iflow\Swoole\Scrapy\http;


use Co\Http\Client;


/**
 * Class http
 * @package iflow\Swoole\Scrapy\http
 */
class http
{

    protected Client|\Co\Http2\Client $client;

    protected mixed $data = null;
    protected array $Queue = [];
    protected array $parse_url = [];

    protected array $param = [];

    public function __construct(
        protected string $host = '',
        protected int $port = 0,
        protected string $method = 'GET',
        protected array $header = [],
        protected bool $isSsl = false,
        protected array $options = [],
        ?callable $call = null
    ){}

    public function addQueue(
        string $host,
        int $port = 0,
        string $method = 'GET',
        array|string $data = "",
        $header = [],
        $isSsl = false,
        $options = [],
        ?callable $call = null
    ){
        $this->Queue[] = [
            'host' => $host,
            'port' => $port,
            'method' => $method,
            'data' => $data,
            'header' => array_replace_recursive($this->header, $header),
            'isSsl' => $isSsl,
            'options' => array_replace_recursive($this->options, $options),
            'call' => $call
        ];
    }

    public function start()
    {
        \Co\run(function () {
            foreach ($this->Queue as $key => $value) {
                $this->process($value);
            }
        });
    }

    public function process(array $data)
    {
        $this
            -> parseUrl($data['host'])
            -> bindQueryParam($data)
            -> initClient($this->param, $data)
            -> setHeader($data['header'])
            -> setData($this->parse_url['query'], $data['data'])
            -> setMethod($data['method']);
        $query = $this -> before($data['call'] ?? null);
        if ($query instanceof self) {
            return $query -> request($this->parse_url['path']);
        }
        return $query;
    }

    public function parseUrl(string $url): static
    {
        $scheme = explode('//', $url)[0];
        if (!preg_match('/^http(s|):$/', $scheme)) {
            $url = "http://" . $url;
        }

        preg_match_all(
            '~^(([^:/?#]+):)?(//([^/?#]*))?([^?#]*)(\?([^#]*))?(#(.*))?~i',
            $url,
            $ex
        );
        $this->parse_url = [
            'scheme' => $ex[2][0] ?? "http",
            'host' => $this->getParas($ex[4][0]),
            'path' => $this->getParas($ex[5][0], "/"),
            'query' => $this->getParas($ex[6][0], ""),
        ];
        return $this;
    }

    public function bindQueryParam($value): static
    {
        if (empty($this->parse_url['host'])) {
            $this->param[] = $value['host'];
            $value['port'] = $value['port'] > 0 ? $value['port'] : 80;
        } else {
            $this->param[] = $this->parse_url['host'];
            $value['port'] = $value['port'] > 0 ? $value['port'] : (
                $this->parse_url['scheme'] === 'https' ? 443 : 80
            );
        }

        $this->param[] = $value['port'];
        $this->param[] = $value['port'] === 443 ? true : $value['isSsl'];
        return $this;
    }

    protected function setData($query, $data): static
    {
        if ($query) {
            parse_str($query, $queryArray);
            $data = array_replace_recursive($queryArray, $data);
        }
        $this->client -> setData($data);
        return $this;
    }

    protected function initClient($param, $options = []): static
    {
        $this->client = new Client(...$param);
        $options['options'] = array_replace_recursive($this->options, $options['options']);
        $this->client -> set($options['options']);
        return $this;
    }

    protected function before($call): static | bool
    {
        // before
        if (is_callable($call)) {
            return call_user_func($call, $this);
        }
        return $this;
    }

    public function request($path = ""): static
    {
        $this->client -> execute($path . $this->parse_url['query']);
        $this->data = $this->client -> body;
        $this->client -> close();
        return $this;
    }

    public function getData()
    {
        return match (gettype($this->data)) {
            'string' => json_decode($this->data, true) ?? $this->data,
            default => $this->data
        };
    }

    public function setHeader(array $header = []): static
    {
        $this->header = array_replace_recursive($this->header, $header) ?? [];
        $this->client -> setHeaders($header);
        return $this;
    }

    public function setProxy(array $proxy = []): static
    {
        $this->client -> set($proxy);
        return $this;
    }

    public function __call(string $name, array $arguments)
    {
        // TODO: Implement __call() method.
        if (method_exists($this->client, $name)) {
            call_user_func([$this->client, $name], ...$arguments);
        }
        return $this;
    }

    public function __get(string $name)
    {
        // TODO: Implement __get() method.
        if (property_exists($this->client, $name)) return $this->client -> $name;
        return null;
    }

    protected function getParas($value, $default = null)
    {
        $value = empty($value) ? $default : $value;
        if ($value === null) {
            throw new \Exception("http request value is null");
        }
        return $value;
    }
}
