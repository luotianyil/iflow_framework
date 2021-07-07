<?php


namespace iflow\Utils\Message;


use iflow\Response;

class Message
{
    use baseMessage;

    protected function message(int $code, $msg = "", $items = [], $page_info = []): array|Response
    {
        $_msg = [ 'code' => $code,  'msg' => $msg, 'items' => $items];
        $_msg = count($page_info) > 0 ? array_merge($page_info, $_msg): $_msg;
        return $this->setData($_msg, $code);
    }

    // 成功
    public function success($msg = "", $items = [], $page_info = []): array|Response
    {
        return $this->message(200, $msg, $items, $page_info);
    }

    // 参数错误
    public function parameter_error($msg = "", $items = []): array|Response
    {
        return $this->message(400, $msg, $items);
    }

    // 未授权
    public function unauthorized_error($msg = "", $items = []): array|Response
    {
        return $this->message(401, $msg, $items);
    }

    // 无数据
    public function nodata($msg = "", $items = []): array|Response
    {
        return $this->message(404, $msg, $items);
    }

    // 更新失败
    public function update_failed($msg = "", $items = []): array|Response
    {
        return $this->message(202, $msg, $items);
    }

    // 服务器处理错误
    public function server_error(int $code = 500, $msg = "", $items = []): array|Response
    {
        return $this->message($code, $msg, $items);
    }

    // 重定向
    public function redirect(string $url): array|Response
    {
        return $this->message(302, 'URL Redirect', [
            'url' => $url
        ]);
    }

}