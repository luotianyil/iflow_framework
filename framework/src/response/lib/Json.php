<?php


namespace iflow\response\lib;


use iflow\Response;

class Json extends Response
{
    public array $options = [
        'json_encode' => JSON_UNESCAPED_UNICODE
    ];

    public string $contentType = 'application/json';

    public function __construct(array $data = [], int $code = 200)
    {
        $this->init($data, $code);
    }

    /**
     * 返回json
     * @param $data
     * @return string
     * @throws \Exception
     */
    protected function output($data) : string
    {
        try {
            $data = json_encode($data, $this->options['json_encode']);
            if (false === $data) throw new \InvalidArgumentException(json_last_error_msg());
            return $data;
        } catch (\Exception $e) {
            if ($e -> getPrevious()) throw $e -> getPrevious();
            throw $e;
        }
    }
}