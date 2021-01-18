<?php


namespace iflow\Utils\Message;


class Message
{
    use baseMessage;

    private function message($code = [], $msg = "", $items = [], $page_info = [])
    {
        $_msg = [ 'code' => $code,  'msg' => $msg, 'items' => $items];
        $_msg = count($page_info) > 0 ? array_merge($page_info, $_msg): $_msg;
        return $this->setData($_msg, $code);
    }

    // 成功
    public function success($msg = "", $items = [], $page_info = [])
    {
        return $this->message(200, $msg, $items, $page_info);
    }

    // 参数错误
    public function parameter_error($msg = "", $items = [])
    {
        return $this->message(400, $msg, $items);
    }

    // 未授权
    public function unauthorized_error($msg = "", $items = [])
    {
        return $this->message(401, $msg, $items);
    }

    // 无数据
    public function nodata($msg = "", $items = [])
    {
        return $this->message(404, $msg, $items);
    }

    // 更新失败
    public function update_failed($msg = "", $items = [])
    {
        return $this->message(202, $msg, $items);
    }

    // 服务器处理错误
    public function server_error($code = 500, $msg = "", $items = [])
    {
        return $this->message($code, $msg, $items);
    }

}