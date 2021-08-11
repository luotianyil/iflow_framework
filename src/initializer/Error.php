<?php


namespace iflow\initializer;


use iflow\App;
use iflow\exception\Handle;
use iflow\exception\lib\errorException;
use iflow\Response;
use Throwable;
// 异常接管
class Error
{
    protected App $app;
    protected array $config = [];
    protected string $handle = Handle::class;

    public function initializer(App $app)
    {
        $this->app = $app;
        $this->config = config('app');
        $this->handle = $this->config['exceptionHandle'] ?: $this->handle;

        // 全部接管
        error_reporting(E_ALL);
        set_exception_handler([$this, 'appHandler']);
        set_error_handler([$this, 'appError']);

        // 错误终止
        register_shutdown_function([$this, 'appShuDown']);
    }

    public function appError(int $errno, string $message, string $file = '', int $line = 0)
    {
        // 致命错误处理
        $exception = new errorException(
            $errno, $message, $file, $line
        );
        if (error_reporting() & $errno) {
            throw $exception;
        }
    }

    public function appHandler(Throwable $e)
    {
        $type = $this->isFatal($e -> getCode()) ? 'warning' : 'error';
        if (class_exists($this->handle)) {
            // 异常处理回调
            $res = (new $this->handle($type)) -> render(
                $this->app, $e
            );
            if ($res instanceof Response) {
                return $res -> send();
            }
        }
        return message() -> server_error(502, 'Server Error') -> send();
    }

    public function appShuDown()
    {}

    // 验证错误类型
    protected function isFatal(int $type): bool
    {
        return in_array($type, [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE]);
    }

}