<?php


namespace iflow\Utils\Message;


trait baseMessage
{
    public string $error = '';
    public string $msg = '';
    public int $code = 200;
    public array $items = [];
    public array $page_info = [];
    public string $filter = 'json';
    public bool $isRest = false;

    public function msgBaseInitialize()
    {
        $this->error = '';
        $this->msg = '';
        $this->items = [];
        $this->page_info = [];
        $this->filter = 'json';
        $this->isRest = false;
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

        if ($this->isRest) {

            // 是否为重定向
            if ($code === 302)
                return response()
                    -> data($data['msg'])
                    -> setRedirect($data['items']['url']);

            // 将请求信息写入 返回数据中
            $data['requestInfo'] = [
                'requestUri' => request() -> request_uri,
                'requestParam' => array_keys(request() -> getParams() ?? []),
                'timestamp' => request() -> server['request_time_float'],
                'method' => request() -> request_method
            ];

            return match ($this->filter) {
                'xml' => xml($data, $code),
                default => json($data, $code)
            };
        }
        return $this->filter === 'json' ? json($data) : xml($data);
    }
}