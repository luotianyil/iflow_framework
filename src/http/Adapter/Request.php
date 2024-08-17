<?php


namespace iflow\http\Adapter;


class Request
{

    public array $server = [];

    public array $get = [];

    public array $post = [];

    public array $header = [];

    public array $files = [];

    public Cookie $cookie;

    public array $request;
    public array|string|null $rowContent = [];

    public string $input;

    public function __construct()
    {
        $this->input = file_get_contents('php://input');
        $this->setServer() -> setHeader() -> setParams() -> setRowContent();
    }

    // 获取原始POST包体
    public function getContent(): array|string|null
    {
        return $this->rowContent ?: $this->post;
    }

    protected function setServer(): static
    {
        $this->server = $this->array_key_lower($_SERVER);
        return $this;
    }

    protected function setHeader(): static {
        if (function_exists('apache_request_headers') && $apache_header = apache_request_headers()) {
            $this->header = array_change_key_case($apache_header, CASE_LOWER);
        }

        foreach ($this->server as $name => $value)  {
            if (str_starts_with($name, 'http_')) {
                $this->header[substr($name, 5)] = $value;
            } else {
                $this->header[str_replace('_', '-', $name)] = $value;
            }
        }

        return $this;
    }

    protected function setParams(): static
    {
        $this->get     = $_GET;
        $this->post    = $_POST;
        $this->request = $_REQUEST;
        $this->cookie  = app(Cookie::class, [
            $_COOKIE
        ], true);
        $this->files   = $_FILES ?? [];
        return $this;
    }

    protected function array_key_lower($array): array {
        $temp = [];
        foreach ($array as $name => $value)
        {
            $temp[strtolower(str_replace('-', '_', $name))] = $value;
        }
        return $temp;
    }

    protected function setRowContent()
    {
        $contentType = $this->header['content-type'] ?? ($this->header['accept'] ?? 'text/html');
        if ('application/x-www-form-urlencoded' === explode(';', $contentType)[0]) {
            parse_str($this->input, $this->rowContent);
        } elseif (str_contains($contentType, 'json')) {
            $this->rowContent = json_decode($this->input, true);
        } else {
            $this->rowContent = $this->input;
        }
    }


    public function getMethod(): string {
        return $this->server['request_method'];
    }
}