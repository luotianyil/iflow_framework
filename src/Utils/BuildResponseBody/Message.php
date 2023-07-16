<?php


namespace iflow\Utils\BuildResponseBody;


use iflow\Container\implement\generate\exceptions\InvokeClassException;
use iflow\Response;

class Message {

    use ResponseMessage;

    /**
     * @param int $code
     * @param string $msg
     * @param array $items
     * @param array $page_info
     * @return array|Response
     * @throws InvokeClassException
     */
    final protected function message(int $code, string $msg = "", array $items = [], array $page_info = []): array|Response {
        $_msg = [ 'code' => $code,  'msg' => $msg, 'items' => $items];
        $_msg = count($page_info) > 0 ? array_merge($page_info, $_msg): $_msg;
        return $this->builderResponseBody($_msg, $code);
    }

    /**
     * 成功
     * @param string $msg
     * @param array $items
     * @param array $page_info
     * @return array|Response
     * @throws InvokeClassException
     */
    public function success(string $msg = "", array $items = [], array $page_info = []): array|Response {
        return $this->message(200, $msg, $items, $page_info);
    }

    /**
     * 参数错误
     * @param string $msg
     * @param array $items
     * @return array|Response
     * @throws InvokeClassException
     */
    public function parameter_error(string $msg = "", array $items = []): array|Response {
        return $this->message(400, $msg, $items);
    }

    /**
     * 未授权
     * @param string $msg
     * @param array $items
     * @return array|Response
     * @throws InvokeClassException
     */
    public function unauthorized_error(string $msg = "", array $items = []): array|Response {
        return $this->message(401, $msg, $items);
    }

    /**
     * 无数据
     * @param string $msg
     * @param array $items
     * @return array|Response
     * @throws InvokeClassException
     */
    public function nodata(string $msg = "", array $items = []): array|Response {
        return $this->message(404, $msg, $items);
    }

    /**
     * 更新失败
     * @param string $msg
     * @param array $items
     * @return array|Response
     * @throws InvokeClassException
     */
    public function update_failed(string $msg = "", array $items = []): array|Response {
        return $this->message(202, $msg, $items);
    }

    /**
     * 服务器处理错误
     * @param int $code
     * @param string $msg
     * @param array $items
     * @return array|Response
     * @throws InvokeClassException
     */
    public function server_error(int $code = 500, string $msg = "", array $items = []): array|Response {
        return $this->message($code, $msg, $items);
    }

    /**
     * 重定向
     * @param string $url
     * @return array|Response
     * @throws InvokeClassException
     */
    public function redirect(string $url): array|Response {
        return $this->message(302, 'URL Redirect', [
            'url' => $url
        ]);
    }

}