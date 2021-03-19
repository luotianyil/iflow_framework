<?php


namespace iflow\http\lib;


class Request
{

    public array $server = [];

    public array $get = [];
    public array $post = [];
    public array $header = [];
    public array $files = [];
    public Cookie $cookie;
    public array $request;

    public string $input;

    public function __construct()
    {
        $this->input = file_get_contents('php://input');
        $this->setServer() -> setHeader() -> setParams();
    }

    public function getContent(): string
    {
        return $this->input;
    }

    protected function setServer(): static
    {
        $this->server = $this->array_key_lower($_SERVER);
        return $this;
    }

    protected function setHeader(): static {
        if (function_exists('apache_request_headers') && $apache_header = apache_request_headers()) {
            $this->header = $apache_header;
        } else {
            foreach ($this->server as $name => $value)
            {
                if (substr($name, 0, 5) == 'http_')
                {
                    $this->header[substr($name, 5)] = $value;
                }
            }
        }

        return $this;
    }

    protected function setParams()
    {
        $this->get     = $_GET;
        $this->post    = $_POST;
        $this->request = $_REQUEST;
        $this->cookie  = app() -> make(Cookie::class, [
            $_COOKIE
        ]);
        $this->files    = $_FILES ?? [];
    }

    protected function array_key_lower($array): array {
        $temp = [];
        foreach ($array as $name => $value)
        {
            $temp[strtolower(str_replace('-', '_', $name))] = $value;
        }
        return $temp;
    }
}