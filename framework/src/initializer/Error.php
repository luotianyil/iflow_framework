<?php


namespace iflow\initializer;


use iflow\App;
use Throwable;
// 异常接管
class Error
{
    protected App $app;

    public function initializer(App $app)
    {
        $this->app = $app;
        // 全部接管
        error_reporting(E_ALL);
        set_exception_handler([$this, 'appHandler']);
        set_error_handler([$this, 'appError']);

        // 错误终止
        register_shutdown_function([$this, 'appShuDown']);
    }

    public function appError(int $errno, string $str, string $file = '', int $line = 0)
    {}

    public function appHandler(Throwable $e)
    {}

    public function appShuDown()
    {}

    // 验证错误类型
    protected function isFatal(int $type): bool
    {
        return in_array($type, [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE]);
    }

}