<?php


namespace iflow\exception;


use iflow\App;
use iflow\exception\Adapter\HttpException;
use iflow\exception\Adapter\HttpResponseException;
use iflow\Response;

class Handle {

    public function __construct(
        protected string $type = "error"
    ) {}

    public function render(App $app, \Throwable $exception): Response {
        if ($exception instanceof HttpResponseException) {
            return $exception -> getResponse();
        } else if ($exception instanceof HttpException) {
            return message() -> server_error($exception -> getStatusCode(), $exception -> getMessage());
        } else {
            // 其他异常时写入日志
            logs($this->type,
                $this->type.
                ": {$exception -> getMessage()} file: {$exception -> getFile()} in line {$exception -> getLine()}"
            );
            return message() -> server_error($exception -> getCode() ?: 502, $exception -> getMessage());
        }
    }
}