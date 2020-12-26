<?php


namespace iflow\Utils\Message;


trait baseMessage
{
    public ?string $error = null;
    public string $msg = "";
    public int $code = 200;
    public array $items = [];
    public array $page_info = [];
    public string $filter = 'json';
    public ?bool $isRest = null;

    public function msgBaseInitialize()
    {
        $this->error = null;
        $this->msg = '';
        $this->items = [];
        $this->page_info = [];
        $this->filter = 'json';
        $this->isRest = null;
        $this->code = 200;
    }

    public function setFilter(string $filter = 'json'): static
    {
        $this->filter = $filter;
        return $this;
    }

    public function setIsRest(bool $isRest = true): static
    {
        $this->isRest = $isRest;
        return $this;
    }

    public function setData(array $data = [], $code = 200)
    {
        if ($this->filter === 'array') {
            return $data;
        }

        if ($this->isRest === null) $this->isRest = config("webconfig.isRest");

        if ($this->isRest) {
            $data['requestInfo'] = [
                'requestUri' => request() -> pathinfo(),
                'requestParam' => array_keys(request() -> param() ?? []),
                'timestamp' => time(),
                'method' => request() -> method()
            ];

            return match ($this->filter) {
                'xml' => xml($data, $code, [], ['root_node' => 'xml']),
                'json' => json($data, $code)
            };
        }
        return $this->filter === 'json' ? json($data) : xml($data, 200, [], ['root_node' => 'xml']);
    }
}