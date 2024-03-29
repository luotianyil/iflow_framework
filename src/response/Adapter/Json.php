<?php


namespace iflow\response\Adapter;


use iflow\Response;

class Json extends Response
{
    public array $options = [
        'json_encode' => JSON_UNESCAPED_UNICODE
    ];

    public function __construct(mixed $data = [], int $code = 200) {
        $this->contentType = 'application/json';
        $this->init($data, $code);
    }

    /**
     * 返回json
     * @param $data
     * @return string
     * @throws \Exception|\Throwable
     */
    public function output($data) : string
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